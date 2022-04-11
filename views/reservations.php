<style>
    body {
        margin: 10px;
    }
</style>

<?php
 /*
  * handling of the reservations operations
 */
 $icon_person = $settings->icons_person;
 $icon_play   = $settings->icons_play;
  			
 // when you click a Submit button on the forms
 // called during add
if (isset($_POST['add'])){
    unset($_POST['add']); // remove the submit button otherwise it will be added to the database
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
    
    // set random client_id
    $ret = $app->crud->read($settings->tables_clients, 'client_id');
    if ($ret['ok']){
        $arr = [];
       foreach($ret['data']['rows'] as $row){
         $arr[] = $row['client_id'];
       }
       
       // random value from an array
       $_POST['client_id'] = $arr[array_rand($arr)];
    } else {
        $_POST['client_id'] = 1; // default
    }
    
    $ret = $app->createReservation($_POST);
    if ($ret['ok']){       
        // go to the reservations area
        die("<script>window.location.href='?view=reservations';</script>");

    } else {
        die('Error: ' . $ret['error']);
    }
}
// called during edit
if (isset($_POST['edit'])){
    unset($_POST['edit']); // remove the submit button otherwise it will be added to the database

    // handling file uploads
    /*
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
    } */
    
    $ret = $app->updateReservation($actionid, $_POST);
    if ($ret['ok']){
        die("<script>window.location.href='?view=reservations';</script>"); // navigate the page to this URL
    } else {
        die('Error: ' . $ret['error']);
    }    
}

// handling of ?view=reservations&action=delete|edit|details|add|no_parameter
 switch ($action){
        case 'add': // add a new reservation
            $mydb = new CForm(  $settings->database_host,
                                $settings->database_user,
                                $settings->database_pwd, 
                                $settings->database_name);

            $metadata = [];
            $ret = $mydb->getTableMetadata($settings->tables_reservations);
            if ($ret['ok']){
                $metadata = $ret['data'];
            } 
            
            // show a data entry form on the screen without any data
            $ret = $mydb->generateForm( $settings->tables_reservations);        
            if ($ret['ok']){ 
                $body     = [];
                $ignored  = ['reservation_id', 'user_id', 'entrydate'];        
                $required = ['name', 'date_checkin', 'date_checkout', 'number_adults','number_children', 'lodge_id']; 
                
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
                                $data = "<input $req type='file' accept='.jpg,.jpeg,.png' name='$id' id='$id'>";
                                break;

                            case 'date_checkin':
                            case 'date_checkout':
                                $data = "<input $req type='date' value=\"$v\" name='$id' id='$id'>";
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
                echo "<form method='post'>
                    <fieldset>
                        <legend>reservation Details</legend>
                        $body
                        <input type='submit' name='add' value='Add Record'>
                    </fieldset>
                    </form>
                    <p>&nbsp;</p>
                    ";
        
            } else {
                if ($ret['error']) echo 'Error: ' . $ret['error'];
            }   
            break;
        
		 case 'edit': // edit the reservation
		    $mydb = new CForm(  $settings->database_host,
                                $settings->database_user,
                                $settings->database_pwd, 
                                $settings->database_name );
		                    
            $metadata = [];
            $ret = $mydb->getTableMetadata($settings->tables_reservations);
            if ($ret['ok']){
                $metadata = $ret['data'];
            } 
            
            // show a data editing form on the screen with data for reservation with reservation_id = $actionid
		    $ret = $mydb->generateForm( $settings->tables_reservations,'reservation_id', $actionid);
		    
		    if ($ret['ok']){ 
		        $body     = [];
		        $ignored  = ['reservation_id', 'user_id', 'entrydate'];        
		        $required = ['name', 'date_checkin', 'date_checkout', 'number_adults','number_children', 'lodge_id']; 
		        
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
                                
                            case 'date_checkin':
                            case 'date_checkout':
                                $data = "<input $req type='date' value=\"$v\" name='$id' id='$id'>";
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
                                
                            case 'client_id':
                                $ret = $mydb->getTableColumnData($settings->tables_clients, 'name', 'client_id');
                                if ($ret['ok']){
                                    $values = '';
                                    
                                    foreach($ret['data'] as $id0=>$val0){
                                        $selected = $id0 == $v ? 'selected' : '';
                                        $values .= "<option value='$id0' $selected>$val0</option>";
                                    }
                                    
                                    $data = "<p><select $req name='$id' id='$id'>$values</select></p>";
                                }           			
                                break;
                                
                            case 'lodge_id':
                                $ret = $mydb->getTableColumnData($settings->tables_lodges, 'name', 'lodge_id');
                                if ($ret['ok']){
                                    $values = '';
                                    
                                    foreach($ret['data'] as $id0=>$val0){
                                        $selected = $id0 == $v ? 'selected' : '';
                                        $values .= "<option value='$id0' $selected>$val0</option>";
                                    }
                                    
                                    $data = "<p><select $req name='$id' id='$id'>$values</select></p>";
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
		                <legend>Edit reservation</legend>
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

       case 'booknow': // add a new reservation
            $mydb = new CForm(  $settings->database_host,
                                $settings->database_user,
                                $settings->database_pwd, 
                                $settings->database_name);

            $metadata = [];
            $ret = $mydb->getTableMetadata($settings->tables_reservations);
            if ($ret['ok']){
                $metadata = $ret['data'];
            } 
            
            // show a data entry form on the screen without any data
            $ret = $mydb->generateForm( $settings->tables_reservations);        
            if ($ret['ok']){ 
                $body     = [];
                $ignored  = ['reservation_id', 'user_id', 'entrydate', 'client_id'];        
                $required = ['name', 'date_checkin', 'date_checkout', 'number_adults','number_children', 'lodge_id']; 
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
                            case 'number_adults':
                            case 'number_children':
                                $data = "<input type='number' $extra $req name='$id' id='$id' value=\"$v\">";
                                break;
                                
                            case 'photo':
                            case 'picture':
                                $data = "<input $req type='file' accept='.jpg,.jpeg,.png' name='$id' id='$id'>";
                                break;
                                
                            case 'date_checkin':
                            case 'date_checkout':
                                $data = "<input $req type='date' value=\"$v\" name='$id' id='$id'>";
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
                                
                            case 'lodge_id':
                                $ret = $mydb->getTableColumnData($settings->tables_lodges, 'name', 'lodge_id');
                                if ($ret['ok']){
                                    $values = '';
                                    $preselectedlodge = (int) @ $_GET['id'];

                                    foreach($ret['data'] as $id0=>$val0){
                                        
                                        if ($preselectedlodge){
                                            $selected = ($id0 == $preselectedlodge) ? 'selected' : '';
                                        } else {
                                            $selected = ($id0 == $v) ? 'selected' : '';
                                        }
                                        $values .= "<option value='$id0' $selected>$val0</option>";
                                    }
                                    
                                    $data = "<p><select $req name='$id' id='$id'>$values</select></p>";
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
                echo "<form method='post'>
                    <fieldset>
                        <legend>Reservation Details</legend>
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
            
        case 'details': // get details of the reservation
            $mydb = new CForm( $settings->database_host,
                               $settings->database_user,
                               $settings->database_pwd, 
                               $settings->database_name );
                            
            $ret = $mydb->generateForm( $settings->tables_reservations,'reservation_id', $actionid);
        
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
                        <legend>Reservation Details</legend>
                        <table>
                         <tbody>$body</tbody>
                        </table>
                       </fieldset>
                      </form>
                      <!--p>&nbsp;</p-->
                      ";
        
                echo "<p class='text-underline'>Playlists linked to this reservation</p>";

                $result = $app->getPlaylistsLinkedToreservation($actionid);
                if ( !$result['ok'] ){
                     echo $settings->error_noplaylists;
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
                           // if ($col == 'Title') $val = "$icon_reservation $val";
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
        
        case 'delete': // delete the reservation
            $ret = $app->deleteReservation($actionid);
            if (!$ret['ok']){
                if ($ret['error']) echo 'Error: ' . $ret['error'];
                
            } else {
                die("<script>window.location.href='?view=reservations';</script>"); // show all reservations
            }
            break;
             
             
     default: // show all the reservations
		echo $settings->html_reservations_title;
		//echo $settings->buttons_newreservation;
		//echo $settings->html_p;
        echo $settings->html_searchbox;
        echo $settings->html_p;
        
		$result = $app->getReservations();
		if (!$result['ok']){
		   echo $settings->error_noreservations;
		   
		} else {
	
			$ignore = ['reservation_id', 'user_id'];
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
				    //if ($col == 'Title') $val = "<a href='#' class='youtube-reservation' onclick=\"queuereservation($idx);\" data-youtube-id=\"$id\">$val</a>";
				    $td .= "<td>$val</td>";
				}       
				// actions 
				$reservationid = $row['reservation_id'];
                $actions = $settings->html_actions_reservations;
                $actions = str_replace('{reservationid}', $reservationid, $actions);
                				
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
                            window.location.href = '?view=reservations&action=delete&id='+id;
                        }
                        return false;
                    }		
                </script>";			
		}   
 }
