<style>
    body {
        margin: 10px;
    }
</style>
<?php
 /*
  * handling of the lodges operations
 */
 $icon_person = $settings->icons_person;
 $icon_play   = $settings->icons_play;
  			
 // when you click a Submit button on the forms
 // called during add
if (isset($_POST['add'])){
    unset($_POST['add']); // remove the submit button otherwise it will be added to the database
    
    // handling file uploads
    foreach ($_FILES as $name => $value) {
        $path = $_FILES[$name]['name'];
        $tmp  = $_FILES[$name]['tmp_name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        // ignore if not an image
        //if (! getimagesize ($tmp)) continue;
        //if (!in_array(strtolower($ext), $imageeext)) continue;

        $random = $app->randomPassword(10);
        $to     = $settings->uploaddir . "$random.$ext";

        if (move_uploaded_file( $tmp, $to)) {
            // value to save in the table
            $_POST[$name] = $to;
        } else {
            echo "Failed to move file $path to $to.<BR>";
        }
    }
    
    // set user_id
    $_POST['user_id'] = 1;
    
    $ret = $app->createLodge($_POST);
    if ($ret['ok']){       
        // go to the lodges area
        //die("<script>window.location.href='?view=lodges';</script>");

    } else {
        die('Error: ' . $ret['error']);
    }
}
// called during edit
if (isset($_POST['edit'])){
    unset($_POST['edit']); // remove the submit button otherwise it will be added to the database

    // handling file uploads

    // we need to delete the existing file.
    // so let's get the current row
    $ret = $app->crud->read($settings->tables_lodges, 'lodge_id', $actionid,1); // 1 record
    if (! $ret['ok']) die('Record not found');
    $row = $ret['data']['rows'][0]; // the record
        
    foreach ($_FILES as $name => $value) {
        if ($_FILES[$name]['error']==4){
            // upload failed 
        } else {
            $path = $_FILES[$name]['name'];
            $tmp  = $_FILES[$name]['tmp_name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            // ignore if not an image
            //if (! getimagesize ($tmp)) continue;
            //if (!in_array(strtolower($ext), $imageeext)) continue;

            $random = $app->randomPassword(10);
            $to     = $settings->uploaddir . "$random.$ext";

            if (move_uploaded_file( $tmp, $to)) {
                // delete existing
                $existing = $row[$name];
                if (file_exists($existing)) @ unlink($existing);
                
                // value to save in the table
                $_POST[$name] = $to;
            } else {
                echo "Failed to move file $path to $to.<BR>";
            }            
        }
    }

    
    $ret = $app->updateLodge($actionid, $_POST);
    if ($ret['ok']){
        //die("<script>window.location.href='?view=lodges';</script>"); // navigate the page to this URL
    } else {
        die('Error: ' . $ret['error']);
    }    
}

// handling of ?view=lodges&action=delete|edit|details|add|no_parameter
 switch ($action){
        case 'add': // add a new lodge
            $mydb = new CForm(  $settings->database_host,
                                $settings->database_user,
                                $settings->database_pwd, 
                                $settings->database_name);

            $metadata = [];
            $ret = $mydb->getTableMetadata($settings->tables_lodges);
            if ($ret['ok']){
                $metadata = $ret['data'];
            } 
            
            // show a data entry form on the screen without any data
            $ret = $mydb->generateForm( $settings->tables_lodges);        
            if ($ret['ok']){ 
                $body     = [];
                $ignored  = ['lodge_id', 'user_id', 'entrydate'];        
                $required = ['name', 'town_id', 'telephone', 'address', 'email','picture']; // these values in the array will have the required property added to the input field
                foreach($ret['data'] as $id=>$v){
                        $c = @$comments[$id] ? $comments[$id] : ucwords($id);
                        $req = in_array($id, $required) ? "required='required'" : "";
                        $requiredstar = $req ? "<span class='color_red'>*</span>" : '';
                        if (in_array($id, $ignored)) continue;
        
                        $type = $metadata[$id]['type'];
                        $size = $mydb->numbersFromString($type);                        
                        $extra = $size ? "maxlength=$size" : '';
                        
                        $data = "<input type='text' $extra $req name='$id' id='$id' value=\"$v\">";
                        /* handle some columns */
                        switch ($id){
                            case 'photo':
                            case 'picture':
                                $data = "<p><input $req type='file' accept='.jpg,.jpeg,.png' name='$id' id='$id'></p>";
                                break;
                                
                            case 'filename':
                                $data = "<input $req type='file' accept='.txt,.pdf,.doc' name='$id' id='$id'>";
                                break;
                                
                            case 'active':
                                $values = '';
                                $opts = [1=>'Active', 0=>'Disabled'];
                                foreach($opts as $id0=>$val0){
                                	$selected = $id0 == 1 ? 'selected' : '';
                                    $values .= "<option value='$id0' $selected>$val0</option>";
                                }
                                
                                $data = "<select $req name='$id' id='$id'>
                                          $values
                                         </select>";
                                break;
                                
                            case 'town_id':
                                $ret = $mydb->getTableColumnData($settings->tables_towns, 'name', 'town_id');
                                if ($ret['ok']){
                                    $values = '';
                                    
                                    foreach($ret['data'] as $id0=>$val0){
                                        $values .= "<option value='$id0'>$val0</option>";
                                    }
                                    
                                    $data = "<p><select $req name='$id'>$values</select></p>";
                                }           			
                                break;
                                
                            case 'email':
                            case 'email_address': 
                                $data = "<input type='email' $req name='$id' id='$id' value=\"$v\">";
                                break;
                                
                            default:
                                break;
                        } /* handle some columns */  
                        
                        if ($id == 'email_address'){
                            $data = "<input type='email' $req name='$id' id='$id' value=\"$v\">";
                        }
                        
                    $body[] = "<p><label for='$id'>$c $requiredstar</label><BR>$data</p>";
                }        
                $body = implode('', $body);
                
                echo $settings->html_p;
                echo "<form method='post' enctype='multipart/form-data'>
                    <fieldset>
                        <legend>lodge Details</legend>
                        $body
                        <input type='submit' name='add'value='Add Record'>
                    </fieldset>
                    </form>
                    <p>&nbsp;</p>
                    ";
        
            } else {
                if ($ret['error']) echo 'Error: ' . $ret['error'];
            }   
            break;
        
		 case 'edit': // edit the lodge
		    $mydb = new CForm(  $settings->database_host,
                                $settings->database_user,
                                $settings->database_pwd, 
                                $settings->database_name );
		                    
            $metadata = [];
            $ret = $mydb->getTableMetadata($settings->tables_lodges);
            if ($ret['ok']){
                $metadata = $ret['data'];
            } 
            
            // show a data editing form on the screen with data for lodge with lodge_id = $actionid
		    $ret = $mydb->generateForm( $settings->tables_lodges,'lodge_id', $actionid);
		    
		    if ($ret['ok']){ 
		        $body     = [];
		        $ignored  = ['lodge_id', 'user_id', 'entrydate'];        
		        $required = ['name', 'town_id', 'telephone', 'address', 'email']; 
		        
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
                                $preview = file_exists($v) ? "<BR><img style='width:350px; margin: 10px 10px;' src='$v'>" : '';
                                $data = "<input $req type='file' accept='.jpg,.jpeg,.png' name='$id' id='$id'>$preview";
                                break;
                                
                            case 'filename':
                                $data = "<input $req type='file' accept='.txt,.pdf,.doc' name='$id' id='$id'>";
                                break;
                                
                            case 'active':
                                $values = '';
                                $opts = [1=>'Active', 0=>'Disabled'];
                                foreach($opts as $id0=>$val0){
                                	$selected = $id0 == $v ? 'selected' : '';
                                    $values .= "<option value='$id0' $selected>$val0</option>";
                                }
                                
                                $data = "<select $req name='$id' id='$id'>
                                          $values
                                         </select>";
                                break;
                                
                            case 'town_id':
                                $ret = $mydb->getTableColumnData($settings->tables_towns, 'name', 'town_id');
                                if ($ret['ok']){
                                    $values = '';
                                    
                                    foreach($ret['data'] as $id0=>$val0){
                                        $selected = $val0 == $v ? 'selected' : '';
                                        $values .= "<option value='$id0' $selected>$val0</option>";
                                    }
                                    
                                    $data = "<p><select $req id='$id'>$values</select></p>";
                                }           			
                                break;
                                
                            case 'email':
                            case 'email_address': 
                                $data = "<input type='email' $req name='$id' id='$id' value=\"$v\">";
                                break;
                                
                            default:
                                break;
                        } /* handle some columns */  

		                $body[] = "<p><label for='$id'>$c $requiredstar</label><BR>$data</p>";
		        }		
		        
		        $body = implode('', $body);
		
		        echo "<form method='post' enctype='multipart/form-data'>
		               <fieldset>
		                <legend>Edit lodge</legend>
		                $body
                        <input type='submit' name='edit' value='Update Record'>               
		               </fieldset>
		              </form>
		              <p>&nbsp;</p>
		              ";
		
		    } else {
                if ($ret['error']) echo 'Error: ' . $ret['error'];
		    }    
		    break; 

        case 'details': // get details of the lodge
            $mydb = new CForm( $settings->database_host,
                               $settings->database_user,
                               $settings->database_pwd, 
                               $settings->database_name );
                            
            $ret = $mydb->generateForm( $settings->tables_lodges,'lodge_id', $actionid);
        
            if ($ret['ok']){
                $body     = [];
                $ignored  = ['id'];        
                $required = []; // these values in the array will have the required property added to the input field
                foreach($ret['data'] as $id=>$v){
                        $c = @$comments[$id] ? $comments[$id] : ucwords($id);
                        $req = in_array($id, $required) ? "required='required'" : "";
                        $requiredstar = $req ? "<span class='color_red'>*</span>" : '';
                        if (in_array($id, $ignored)) continue;
        
                        $data = "<span>$v</span>";
                        $body[] = "<tr>
                                     <th><label for='$id'>$c $requiredstar</label></th><td>$data</td>
                                   </tr>";
                }    
                $body = implode('', $body);
        
                echo "<form method='post'>
                       <fieldset>
                        <legend>Lodge Details</legend>
                        <table>
                         <tbody>$body</tbody>
                        </table>
                       </fieldset>
                      </form>
                      <!--p>&nbsp;</p-->
                      ";
        
                echo "<p class='text-underline'>Reservations for this lodge</p>";

                $result = $app->getReservationsForLodge($actionid);
                if ( !$result['ok'] ){
                     echo $settings->error_noreservations;
                     if ($result['error']) echo $result['error'];
                } else {
    
                    $cols = $result['data']['cols'];
                    $rows = $result['data']['rows'];
                    $th = ''; $td = '';
    
                    foreach($cols as $col){
                        if ($col == 'playlist_id') continue;
                        $th .= "<th>$col</th>";
                    }
                
                    foreach($rows as $row){
                        $td .= "<tr>";
                        foreach($cols as $col){
                            if ($col == 'playlist_id') continue;
                            $val = $row[$col];
                           // if ($col == 'Title') $val = "$icon_lodge $val";
                            $td .= "<td>$val</td>";
                        }       
                        $td .= "</tr>";
                    }    
                    echo "<table>";
                    echo "<thead><tr>$th</tr></thead>";
                    echo "<tbody>$td</tbody>";
                    echo "</table>";
    
                } 

            } else {
                if ($ret['error']) echo 'Error: ' . $ret['error'];
            }    
            break;     
        
        case 'delete': // delete the lodge
            $ret = $app->deletelodge($actionid);
            if (!$ret['ok']){
                if ($ret['error']) echo 'Error: ' . $ret['error'];
                
            } else {
                die("<script>window.location.href='?view=lodges';</script>"); // show all lodges
            }
            break;
             
             
     default: // show all the lodges
		echo $settings->html_lodges_title;
		echo $settings->buttons_newlodge;
		echo $settings->html_p;
        echo $settings->html_searchbox;
        echo $settings->html_p;
        
		$result = $app->getLodges();
		if (!$result['ok']){
		   echo $settings->error_nolodges;
		   
		} else {
	
			$ignore = ['lodge_id', 'user_id', 'picture'];
			$cols = $result['data']['cols'];
			$rows = $result['data']['rows'];
			$th = ''; $td = '';

			foreach($cols as $col){
			    if (in_array($col, $ignore)) continue; // don't show this field on the html form
				$th .= "<th>$col</th>";
			}
			// extra th
			$th .= "<th>Actions</th>";

			$idx = 0;
			
			foreach($rows as $row){
				$td .= "<tr>";
 				
				foreach($cols as $col){
				    if (in_array($col, $ignore)) continue; // don't show this field on the html form
  
				    $val = $row[$col];
				    //if ($col == 'Title') $val = "<a href='#' class='youtube-lodge' onclick=\"queuelodge($idx);\" data-youtube-id=\"$id\">$val</a>";
				    $td .= "<td>$val</td>";
				}       
				// actions 
				$lodgeid = $row['lodge_id'];
                $actions = $settings->html_actions_lodges;
                $actions = str_replace('{lodgeid}', $lodgeid, $actions);
                				
				$td .= "<td>$actions</td>";
				$td .= "</tr>";
				$idx++;
			}    
			echo "<table>";
			echo "<thead><tr>$th</tr></thead>";
			echo "<tbody>$td</tbody>";
			echo "</table>";   
			
			// use javascript to confirm if we want to delete record
            echo "<script>
                    function confirmDelete(id){
                        if (confirm('Delete the selected record?')){
                            window.location.href = '?view=lodges&action=delete&id='+id;
                        }
                        return false;
                    }		
                </script>";			
		}   
 }
