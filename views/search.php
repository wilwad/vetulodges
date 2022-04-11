<style>
    .youtuberesponsive {width:100%;height:400px;}

    @media screen and (max-width: 450px) {
    .youtuberesponsive {width:100%;height:180px;}
    }

    @media screen and (max-width: 768px) {
    .youtuberesponsive {width:100%;height:325px;}
    }
</style>
<?php
 /*
 * handling of the playlists operations
 */
 // get the icon
 $icon_person = $settings->icons_person;
 $icon_link   = $settings->icons_link;
 $icon_unlink = $settings->icons_unlink;
 $icon_play   = $settings->icons_play;
 $icon_video  = $settings->icons_video;
  
 // when you click a Submit button on the forms
 // called during add
if (isset($_POST['add'])){
    unset($_POST['add']); // remove the submit button otherwise it will be added to the SQL

    // handling automated SPAM
    if (!isset($_POST[ $settings->antispam_post ])){
        die($settings->antispam_error);
    }    
    if ($_POST[ $settings->antispam_post ] !== $settings->antispam_pin){
        die($settings->antispam_error);
    }   
    unset($_POST[ $settings->antispam_post ]);
    
    // handling file uploads
    /*
    if (isset($_FILES)){        
        // we need to delete the existing file.
        // so let's get the current row
        $ret = $mydb->read('girls', 'id', $edit,1); // 1 record
        if (! $ret['ok']) die('Record not found');
        $row = $ret['data']['rows'][0]; // the record

        foreach ($_FILES as $name => $value) {
            $path = $_FILES[$name]['name'];
            $tmp  = $_FILES[$name]['tmp_name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            // ignore if not an image
            //if (! getimagesize ($tmp)) continue;
            //if (!in_array(strtolower($ext), $imageeext)) continue;

            $random = $app->randomPassword(10);
            $to     = $settings->$uploaddir . "$random.$ext";

            if (move_uploaded_file( $tmp, $to)) {
                // only 1 record is returned
                $fileexisting = $row[$name];

                if (file_exists($fileexisting)){
                    @ unlink($fileexisting);
                    echo "Deleted $fileexisting";
                } 

                // value to save in the table
                $_POST[$name] = $to;
            } else {
                echo "Failed to move file $path to $to.<BR>";
            }
        }
    } */
    
    // the playlist code is generated manually
    /*
    $playlistname = $_POST['name'];
    $generatedplaylistcode = $app->generateClientCode( $playlistname );
    $_POST['playlist_code'] = $generatedplaylistcode;
    */
    $ret = $app->createPlaylist( $_POST );
    if ($ret['ok']){
        // go to the playlists area
        die("<script>window.location.href='?view=playlists';</script>");

    } else {
        die('Error: ' . $ret['error']);
    }
}
// called during edit
if (isset($_POST['edit'])){
    unset($_POST['edit']); // remove the submit button otherwise it will be added to the SQL

    // handling automated SPAM
    if (!isset($_POST[ $settings->antispam_post ])){
        die($settings->antispam_error);
    }    
    if ($_POST[ $settings->antispam_post ] !== $settings->antispam_pin){
        die($settings->antispam_error);
    }   
    unset($_POST[ $settings->antispam_post ]);
    
    // handling file uploads
    if (isset($_FILES)){        
        foreach ($_FILES as $name => $value) {
            $path = $_FILES[$name]['name'];
            $tmp  = $_FILES[$name]['tmp_name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            // ignore if not an image
            //if (! getimagesize ($tmp)) continue;
            //if (!in_array(strtolower($ext), $imageeext)) continue;

            $random = $app->randomPassword(10);
            $to     = $settings->$uploaddir . "$random.$ext";

            if (move_uploaded_file( $tmp, $to)) {
                // value to save in the table
                $_POST[$name] = $to;
            } else {
                echo "Failed to move file $path to $to.<BR>";
            }
        }
    } 
    
    $playlistid = $actionid;
    $ret = $app->updatePlaylist( $playlistid, $_POST );
    if ($ret['ok']){
        die("<script>window.location.href='?view=playlists';</script>"); // navigate the page to this URL
    } else {
        die('Error: ' . $ret['error']);
    }    
}

// handling of ?view=playlists&action=
// delete|add|edit|linked-videos|link-delete|link-video|link-video-select|no_parameter
 switch ( $action ){
        case 'add': // add a new playlist
            
            $mydb = new CForm( $settings->database_host,
                                $settings->database_user,
                                $settings->database_pwd, 
                                $settings->database_name );

            $metadata = [];
            $ret = $mydb->getTableMetadata($settings->tables_playlists);
            if ($ret['ok']){
                $metadata = $ret['data'];
            }           
            
            // show a data editing form on the screen without any data
            $ret = $mydb->generateForm( $settings->tables_playlists);        
            if ( $ret['ok'] ){ 
                $body     = [];
                $ignored  = ['playlist_id'];        
                $required = ['title']; // these values in the array will have the required property added to the input field
                foreach( $ret['data'] as $id=>$v ){
                        $c = @$comments[$id] ? $comments[$id] : ucwords($id);
                        $req = in_array($id, $required) ? "required='required'" : "";
                        $requiredstar = $req ? "<span class='color_red'>*</span>" : '';
                        if (in_array($id, $ignored)) continue;
        
                        $type = @$metadata[$id]['type'];
                        $size = $mydb->numbersFromString($type);                        
                        $extra = $size ? "maxlength=$size" : '';
                        $data = "<input type='text' $extra $req name='$id' id='$id' value=\"$v\">";
                        
                        /* handle some columns */
                        switch ($id){
                            case 'photo':
                            case 'picture':
                                $data = "<input $req type='file' accept='.jpg,.jpeg,.png' name='$id' id='$id'>";
                                break;
                                
                            case 'filename':
                                $data = "<input $req type='file' accept='.txt,.pdf,.doc' name='$id' id='$id'>";
                                break;
                                
                            case 'author_id':
                                $ret = $movies->getTableColumnData('books_authors', 'name');
                                if ($ret['ok']){
                                    $values = '';
                                    
                                    foreach($ret['data'] as $id0=>$val0){
                                        $values .= "<option value='$id0'>$val0</option>";
                                    }
                                    
                                    $data = "<select $required name='$id' id='$id'>$values</select>";
                                }
                                break;
                                
                            case 'category_id*':
                                $ret = $mydb->getTableColumnData('categories', 'name');
                                if ($ret['ok']){
                                    $values = '';
                                    
                                    foreach($ret['data'] as $id=>$val){
                                        $values .= "<option value='$id'>$val</option>";
                                    }
                                    
                                    $data = "<select $req id='$id'>$values</select>";
                                }           			
                                break;
                            default:
                                break;
                        } /* handle some columns */  
                        
                        $body[] = "<p><label for='$id'>$c $requiredstar</label><BR>$data</p>";
                }     
                // add the antispam
                $body[] = $settings->html_antispam;
                
                $body = implode('', $body); // flatten the array
                
                echo $settings->html_p;
                echo "<form method='post' enctype='multipart/form-data'>
                        <fieldset>
                            <legend>Playlist Details</legend>
                            $body
                            <input type='submit' name='add'value='Add Record'>
                        </fieldset>
                        </form>
                        <p>&nbsp;</p>
                        ";
        
            } else {
                echo 'Error: ' . $ret['error'];
            }  
            break;
            
        case 'edit': // edit the playlist
            $mydb = new CForm(  $settings->database_host,
                                $settings->database_user,
                                $settings->database_pwd, 
                                $settings->database_name);
                                
            $metadata = [];
            $ret = $mydb->getTableMetadata($settings->tables_playlists);
            if ($ret['ok']){
                $metadata = $ret['data'];
            } 
            
            // show a data editing form on the screen filled with data for playlist with playlist_id = $id
            $ret = $mydb->generateForm( $settings->tables_playlists,'playlist_id', $actionid);
            if  ( $ret['ok'] ){ 
                $body     = [];
                $ignored  = ['playlist_id'];        
                $required = ['name'];
                foreach($ret['data'] as $id=>$v){
                        $c = @$comments[$id] ? $comments[$id] : ucwords($id);
                        $req = in_array($id, $required) ? "required='required'" : "";
                        $requiredstar = $req ? "<span class='color_red'>*</span>" : '';
                        if (in_array($id, $ignored)) continue;
        
                        $type = @$metadata[$id]['type'];
                        $size = $mydb->numbersFromString($type);                        
                        $extra = $size ? "maxlength=$size" : '';
                        $data = "<input type='text' $extra $req name='$id' id='$id' value=\"$v\">";
                        
                        /* handle some columns */
                        switch ($id){
                            case 'photo':
                            case 'picture':
                                $data = "<input $req type='file' accept='.jpg,.jpeg,.png' name='$id' id='$id'>";
                                break;
                                
                            case 'filename':
                                $data = "<input $req type='file' accept='.txt,.pdf,.doc' name='$id' id='$id'>";
                                break;
                                
                            case 'author_id':
                                $ret = $movies->getTableColumnData('books_authors', 'name');
                                if ($ret['ok']){
                                    $values = '';
                                    
                                    foreach($ret['data'] as $id0=>$val0){
                                        $values .= "<option value='$id0'>$val0</option>";
                                    }
                                    
                                    $data = "<select $required name='$id' id='$id'>$values</select>";
                                }
                                break;
                                
                            case 'category_id*':
                                $ret = $mydb->getTableColumnData('categories', 'name');
                                if ($ret['ok']){
                                    $values = '';
                                    
                                    foreach($ret['data'] as $id=>$val){
                                        $values .= "<option value='$id'>$val</option>";
                                    }
                                    
                                    $data = "<select $req id='$id'>$values</select>";
                                }           			
                                break;
                            default:
                                break;
                        } /* handle some columns */  
                        
                        $body[] = "<p><label for='$id'>$c $requiredstar</label><BR>$data</p>";
                }      
                // add the antispam
                $body[] = $settings->html_antispam;
                
                $body = implode('', $body); // flatten this array
        
                echo "<form method='post' enctype='multipart/form-data'>
                        <fieldset>
                            <legend>Edit Playlist</legend>
                            $body
                            <input type='submit' name='edit' value='Update Record'>               
                        </fieldset>
                        </form>
                        <p>&nbsp;</p>
                        ";
        
            } else {
                echo 'Error: ' . $ret['error'];
            }    
            break;

        case 'delete': // delete the playlist
            $result = $app->deletePlaylist($actionid);
            if ( !$result['ok'] ){
                echo 'Error: ' . $result['error'];
            } else {
                die("<script>window.location.href='?view=playlists';</script>");
            }
            break;
            
        case 'linked-videos': // show all the videos linked to the playlist        
            // get details of this playlist first to show on the form
            $result = $app->crud->read($settings->tables_playlists, 'playlist_id', $actionid);
            if ( !$result['ok'] ){
                die("Playlist with id $actionid was not found");
                
            } else {
                
    echo '<div id="video-placeholder" class="youtuberesponsive"></div>'; 
          
                $html_hint_autoplay = $settings->html_hint_autoplay;
                $name = $result['data']['rows'][0]['title'];// only 1 record
                echo "<h2>Playlist: $name</h2>  
                      $html_hint_autoplay
                      <a href='?view=playlists&action=link-video-select&id=$actionid' class='button'>$icon_link Link a new Video</a>";
                echo $settings->html_p;
            }

            $result = $app->getVideosLinkedToPlaylist($actionid);
            if ( !$result['ok'] ){
                 echo $settings->error_nolinkedvideos;
                 
            } else {
          
                $cols = $result['data']['cols'];
                $rows = $result['data']['rows'];
                $th = ''; $td = '';

				$ignore = ['video_id', 'psid', 'url' ];
				
                foreach($cols as $col){
                    if (in_array($col, $ignore) > -1) continue;                 
                    $th .= "<th>$col</th>";
                }
                // extra th
                $th .= "<th>Actions</th>";
				$idx = 0;
                foreach($rows as $row){
                    $td .= "<tr>";
                    $vidid = $row['video_id'];
                    $url = $row['url'];

		            // handling short URL
		            if (strpos($url, 'https://youtu.be') > -1) {
		                $id = str_replace('https://youtu.be/', '', $url);
		            } else {
		                $id    = explode('=',$url)[1];
		                // sometimes id includes playlist so remove that
		                if ( strpos($id,'&') != -1){
		                	$id = explode('&', $id)[0]; // first index
		                }
		            }
                                        
                    foreach($cols as $col){
	                    if (in_array($col, $ignore) > -1) continue;
                        $val = $row[$col];
                        
                        if ($col == 'Title') $val = "<a href='#' class='youtube-video' data-youtube-id=\"$id\" onclick=\"queueVideo($idx);\">$val</a>";
                        $td .= "<td>$val</td>";
                    }       
                    // actions 
                    $psid = $row['psid'];
                    $action = "<a href='?view=videos&action=edit&id=$vidid'>Edit</a> /
                               <a href='?view=playlist&action=link-delete&id=$actionid&psid=$psid'>Remove</a>";
                    $td .= "<td>$action</td>";
                    $td .= "</tr>";
                    $idx++;
                }    

                echo "<table>";
                echo "<thead><tr>$th</tr></thead>";
                echo "<tbody>$td</tbody>";
                echo "</table>";

            }   
            ?>
 <script>
var currIdx = 0;
var player;   
var videos = [];
document.querySelectorAll('.youtube-video').forEach(el=>videos.push(el.getAttribute('data-youtube-id')));

var queueVideo = function (idx){
		if (idx >= videos.length) idx = 0;
		
		let url = videos[idx]
		document.querySelectorAll('a.youtube-video').forEach((el,idx0)=>{
            if (idx0 == idx) 
                el.classList.add('playing')
            else
                el.classList.remove('playing')
            });
		player.cueVideoById(url);
		currIdx = idx
}

var onYouTubeIframeAPIReady = function () {
								if (!videos.length) return;
                                player = new YT.Player('video-placeholder', {
                                                        width: "100%",
                                                        height: "100%",
                                                        videoId: videos[0],
                                                        playerVars: {
                                                                    autoplay: 1,
                                                                    loop: 1,
                                                                    controls: 1,
                                                                    showinfo: 0,
                                                                    autohide: 1,        
                                                                    color: 'white'/*,
                                                                    playlist: 'taJ60kskkns,FG0fTKAqZ5g'*/
                                                        },
                                                        events: {
                                                            'onReady': onPlayerReady,
                                                            'onStateChange': onPlayerStateChange            
                                                        }
                                                    });
}

// 4. The API will call this function when the video player is ready.
function onPlayerReady(event) {}

const playerstates = {5: 'loaded', 
		              3: 'buffering',
		              1: 'playing',
		              2: 'paused',
		              '-1':'stopped',
		              0:'ended'}
                    
function onPlayerStateChange(event) {
		console.log (event.data, playerstates[event.data])
        console.log( playerstates[event.data] )
		
		if (playerstates[event.data] == 'loaded'){
            // video can now be played
            player.playVideo()
        }
        
		if (playerstates[event.data] == 'ended'){
            // queue next video
			currIdx +=1
			queueVideo(currIdx)
		}
}
</script>
            
            <?php
            break;     

        case 'link-video-select': // choose a video to link to this playlist
            // get details of this playlist
            $result = $app->crud->read($settings->tables_playlists, 'playlist_id', $actionid);
            if ( !$result['ok'] ){
                die("Playlist with id $actionid was not found");

            } else {
                // only 1 record is returned, so it will be at $result['data']['rows'][0]
                $name = $result['data']['rows'][0]['title']; 
                echo "<h2>Select a video to link to $name</h2>";
                echo $settings->html_p;
                echo $settings->html_searchbox;
                echo $settings->html_p;                
            }

            // show all videos not linked to this playlist
            $result = $app->getVideosNotLinkedToPlaylist($actionid);
            if (!$result['ok']){
                echo $settings->error_nolinkablevideos;
                    
            } else {

                $cols = $result['data']['cols'];
                $rows = $result['data']['rows'];
                $th = ''; $td = '';

                foreach($cols as $col){
                    if ($col == 'video_id') continue;
                    $th .= "<th>$col</th>";
                }
                // extra th
                $th .= "<th>Actions</th>";
                
                foreach($rows as $row){
                    $td .= "<tr>";
                    foreach($cols as $col){
                        if ($col == 'video_id') continue;
                        $val = $row[$col];
                        if ($col == 'Full Name') $val = "$icon_person $val";
                        $td .= "<td>$val</td>";
                    }       
                    // actions 
                    $id = $row['video_id'];
                    $td .= "<td><a href='?view=$view&action=link-video&videoid=$id&playlistid=$actionid'>Link this video</a></td>";
                    $td .= "</tr>";
                }    
                echo "<table>";
                echo "<thead><tr>$th</tr></thead>";
                echo "<tbody>$td</tbody>";
                echo "</table>";
            }   
            break;   
        
        case 'link-video': // link the chosen video to this playlist
            $videoid = (int) @ $_GET['videoid'];
            $playlistid = (int) @ $_GET['playlistid'];

            // make sure we don't have an existing entry for this
            $sql = $settings->sql_findlinkbetweenplaylistvideo;
            
            // replace the placeholders in the SQL string with real values
            $sql = str_replace('{table}', $settings->tables_joined, $sql);
            $sql = str_replace('{playlistid}', $playlistid, $sql);
            $sql = str_replace('{videoid}', $videoid, $sql);
                            
            $result = $app->crud->readSQL( $sql );
            if (!$result['ok']){
                echo 'Error: ' . $result['error'];
            } else {
                if ( $result['total_rows']){
                    die($settings->error_duplicatelink);
                }
            }

            $result = $app->createLink($videoid,$playlistid);
            if (!$result['ok']){
                echo 'Error: ' . $result['error'];
            } else {
                // show the current linked videos for this playlist
                $url = "<script>window.location.href='?view=playlists&action=linked-videos&id=$playlistid';</script>";
                die($url);
            }
            break;
                    
        case 'link-delete':// delete the link between the playlist and video
            $psid = (int) @ $_GET['psid'];
            $playlistid= (int) @ $_GET['id'];
            
            $result = $app->removeLink($psid);
            if (!$result['ok']){
                echo 'Error: ' . $result['error'];
            } else {
                // show the current linked videos for this playlist
                $url = "<script>window.location.href='?view=playlists&action=linked-videos&id=$playlistid';</script>";
                die($url);
            }
            break;
                    
      default: // show all the playlists 
            echo $settings->html_playlists_title;
            echo $settings->buttons_newplaylist;
            echo $settings->html_p;
            echo $settings->html_searchbox;
            echo $settings->html_p;
        
            $result = $app->getPlaylists();
            if (!$result['ok']){
                echo $settings->error_noplaylists;
            
            } else {

                $cols = $result['data']['cols'];
                $rows = $result['data']['rows'];

                $th = ''; $td = '';

                // headers for the table
                foreach($cols as $col){
                    if ($col == 'playlist_id') continue; // don't show this field on the html form
                    $th .= "<th>$col</th>";
                }
                // extra table header
                $th .= "<th>Actions</th>";

                // font awesome icon
                $icon_bank = $settings->icons_list;
                
                // data for the table
                foreach($rows as $row){
                    $td .= "<tr>";
                    $playlistid = $row['playlist_id'];   
                    
                    foreach($cols as $col){
                        if ($col == 'playlist_id') continue; // don't show this field on the html form            
                        $val = $row[$col];
                        if ($col == 'Name'){
                         $val = "<a href='?view=playlists&action=linked-videos&id=$playlistid'>$val</a>";
                         $val = "$icon_bank $val";
                        }
                        $td .= "<td>$val</td>";
                    } 
                    
                       
                    
                    // extra data for actions 
                    $actions = $settings->html_actions_playlists;
                    $actions = str_replace('{playlistid}', $playlistid, $actions);
                    
                    $td .= "<td>$actions</td>";
                    $td .= "</tr>";
                }    
                echo "<table>";
                echo "<thead><tr>$th</tr></thead>";
                echo "<tbody>$td</tbody>";
                echo "</table>";
                
                // use javascript to confirm if we want to delete record
                echo "<script>
                        function confirmDelete(id){
                            if (confirm('Delete the selected record?')){
                                window.location.href = '?view=playlists&action=delete&id='+id;
                            }
                            return false;
                        }		
                    </script>";
            }                     
 }
