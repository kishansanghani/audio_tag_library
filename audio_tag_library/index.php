<form method="post" enctype="multipart/form-data">
	        <input type="file" name="my_file" multiple>
            <input type="submit" name="submit" value="Upload">
</form>

<?php
if (isset($_POST['submit']))
{
		$myFile = $_FILES['my_file'];
		$fileCount = count($myFile["name"]);
		
		$image_array=array();
		
		//target directory for image save
		$target_dir = "uploaded_songs/";
	  
		//filename
		$fname = $myFile["name"];
	  
		//filename changed with time function
		$target_file = $target_dir . time() . $fname;  
	  
		//move uploaded file in to target folder
		if(move_uploaded_file($myFile["tmp_name"],$target_file))
		{
	  
			//song path
			$song_path     = $target_file;
			
			require_once('audio_data/class/demo.audioinfo.class.php');

			$audio	= new AudioInfo();
			getid3_lib::IncludeDependency('audio_data/class/' . 'write.php', __FILE__, true);
			
			 /**
			 * Chnage album name after upload
			 */
			$getID3 = new getID3;
			$OldThisFileInfo = $getID3->analyze($song_path);
			
			getid3_lib::CopyTagsToComments($OldThisFileInfo);

			$ValidTagTypes = array();
			if(array_key_exists('fileformat', $OldThisFileInfo)) {
				switch ($OldThisFileInfo['fileformat']) {
					case 'mp3':
					case 'mp2':
					case 'mp1':
						$ValidTagTypes = array('id3v1', 'id3v2.3', 'ape');
						break;

					case 'mpc':
						$ValidTagTypes = array('ape');
						break;

					case 'ogg':
						if (!empty($OldThisFileInfo['audio']['dataformat']) && ($OldThisFileInfo['audio']['dataformat'] == 'flac')) {
							//$ValidTagTypes = array('metaflac');
							// metaflac doesn't (yet) work with OggFLAC files
							$ValidTagTypes = array();
						} else {
							$ValidTagTypes = array('vorbiscomment');
						}
						break;

					case 'flac':
						$ValidTagTypes = array('metaflac');
						break;

					case 'real':
						$ValidTagTypes = array('real');
						break;

					default:
						$ValidTagTypes = array();
						break;
				}
			}
			if (isset($OldThisFileInfo['tags'])) {
				$TagFormatsToWrite = array_keys($OldThisFileInfo['tags']);
			}
			if (!empty($TagFormatsToWrite)) {
				$TagFormatsToWrite_final = array();

				foreach ($ValidTagTypes as $ValidTagType) {
					if (count($ValidTagTypes) == 1) {
						$TagFormatsToWrite_final[] = $ValidTagType;
					} else {
						switch ($ValidTagType) {
							case 'id3v2.2':
							case 'id3v2.3':
							case 'id3v2.4':
								if (isset($OldThisFileInfo['tags']['id3v2'])) {
									$TagFormatsToWrite_final[] = $ValidTagType;
								}
								break;

							default:
								if (isset($OldThisFileInfo['tags'][$ValidTagType])) {
									$TagFormatsToWrite_final[] = $ValidTagType;
								}
								break;
						}
					}
				}
			} else {
				$TagFormatsToWrite_final = $ValidTagTypes;
			}
			if (!empty($TagFormatsToWrite_final)) {
				$tagwriter = new getid3_writetags;
				$tagwriter->filename = $song_path;
				$tagwriter->tagformats = $TagFormatsToWrite_final;
				$tagwriter->overwrite_tags = true;
				$tagwriter->remove_other_tags = false;
				$tagwriter->tag_encoding = 'UTF-8';

				$commonkeysarray = array('title', 'artist', 'year', 'comment');
				foreach ($commonkeysarray as $key) {
					if (!empty($OldThisFileInfo['comments'][$key])) {
						$TagData[strtolower($key)][] = implode(', ', $OldThisFileInfo['comments'][strtolower($key)]);
					}
				}
				$TagData['album'][] = 'www.animex44.blogspot.in';
				$tagwriter->tag_data = $TagData;
				if ($tagwriter->WriteTags()) {
//                                            if (!empty($tagwriter->warnings)) {
//                                                echo 'There were some warnings:<BLOCKQUOTE STYLE="background-color:#FFCC33; padding: 10px;">' . implode('<br><br>', $tagwriter->warnings) . '</BLOCKQUOTE>';
//                                            }
				}
			}
			
			$audioDtls	= $audio->Info($song_path);

			echo "<pre>";
			print_r($audioDtls);

			$audioTagDtls	= array();
			if( isset($audioDtls['tags']['id3v1']) ){

				$audioTagDtls[]	= $audioDtls['tags']['id3v1'];

			}

			elseif( isset($audioDtls['tags']['id3v2']) ){

				$audioTagDtls[]	= $audioDtls['tags']['id3v2'];

			}

			elseif( isset($audioDtls['tags']['ID3v2.4']) ){

				$audioTagDtls[]	= $audioDtls['tags']['ID3v2.4'];

			}

			elseif( isset($audioDtls['tags']['ID3v2.2']) ){

				$audioTagDtls[]	= $audioDtls['tags']['ID3v2.2'];

			}

			elseif( isset($audioDtls['tags']['ID3v2.3']) ){

				$audioTagDtls[]	= $audioDtls['tags']['ID3v2.3'];

			}

			elseif( isset($audioDtls['tags']['ID3v3']) ){

				$audioTagDtls[]	= $audioDtls['tags']['ID3v3'];

			}


			echo "<pre>";
			print_r($audioTagDtls);



			$audioalbum 						= ( isset($audioTagDtls[0]['album']) && !empty($audioTagDtls[0]['album']) )?$audioTagDtls[0]['album']:array();

			$audiotitle 						= ( isset($audioTagDtls[0]['title']) && !empty($audioTagDtls[0]['title']) )?$audioTagDtls[0]['title']:array();

			$audioband 						    = ( isset($audioTagDtls[0]['band']) && !empty($audioTagDtls[0]['band']) )?$audioTagDtls[0]['band']:array();

			$audioartist 						= ( isset($audioTagDtls[0]['artist']) && !empty($audioTagDtls[0]['artist']) )?$audioTagDtls[0]['artist']:array();

			$audioyear 							= ( isset($audioTagDtls[0]['year']) && !empty($audioTagDtls[0]['year']) )?$audioTagDtls[0]['year']:array();								

			$audiogenre 						= ( isset($audioTagDtls[0]['genre']) && !empty($audioTagDtls[0]['genre']) )?$audioTagDtls[0]['genre']:array();

			$audiocomposer 						= ( isset($audioTagDtls[0]['composer']) && !empty($audioTagDtls[0]['composer']) )?$audioTagDtls[0]['composer']:array();

			$audiolyricist 						= ( isset($audioTagDtls[0]['lyricist']) && !empty($audioTagDtls[0]['lyricist']) )?$audioTagDtls[0]['lyricist']:array();


			if(!empty($audioalbum))
								{										

									for($al=0; $al<count($audioalbum); $al++)
									{

										$audioAlbumDtlsArr	= array();

										$audioAlbumDtlsArr['attribute_key'] 			= 'album';

										$audioAlbumDtlsArr['attribute_val'] 			= $audioalbum[$al];

									}

								}

			echo "<pre>";
			print_r($audioAlbumDtlsArr);

	  
		}
		else
		{
			echo "Uploading failed";
		}
}
?>

