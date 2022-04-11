<?php
/*
 * class to hold configuration data for the project
*/
class settings {
        public $title          = "Vetu's Lodges";     
        public $showPHPerrors  = true;
        
        // database connection
        public $database_host  = 'localhost';
        public $database_name  = 'goodnez_vetulodges';
        public $database_user  = 'goodnez_vetu';
        public $database_pwd   = 'q!CyCn3(DT{q';

        public $uploaddir      = 'uploads/';
         // tables
        public $tables_lodges  = 'lodges';
        public $tables_clients = 'clients';
        public $tables_users   = 'users';          
        public $tables_reservations = 'reservations';
        public $tables_towns   = 'towns';        
                                
         // errors
         
        public $error_nolodges       = "No lodges found.";
        public $error_noclients      = "No clients found.";
        public $error_noreservations = "No reservations found.";
        public $error_notowns        = "No towns found.";

		// buttons
        public $buttons_home     = "<a href='?view=home'>Home</a>";
        public $buttons_lodges   = "<a href='?view=lodges'>Lodges</a>";
        public $buttons_reservations = "<a href='?view=reservations'>Reservations</a>";
        public $buttons_clients  = "<a href='?view=clients'>Clients</a>";
        
        public $buttons_newlodge  = "<a href='?view=lodges&action=add' class='button'>+ Add new lodge</a><BR>";
        public $buttons_newreservation = "<a href='?view=reservations&action=add' class='button'>+ Add new reservation</a><BR>";
        public $buttons_newclient = "<a href='?view=clients&action=add' class='button'>+ Add new client</a><BR>";
    		
		// HTML 
		public $html_author         = "<small class='float-right'>mockup create by William Sengdara &copy; 2022</small>";   
		public $html_cta_lodges    = "<h3 class='margin10 align-center'>Make a reservation at one of our prime lodges today!</h3>";
		public $html_lodges_title  = "<h1 class='align-center'>All Lodges (<small>Only seen by Administration</small>)</h1>";
		public $html_reservations_title = "<h1 class='align-center'>All Reservations (<small>Only seen by Administration</small>)</h1>";
		public $html_clients_title = "<h1 class='align-center'>All Clients (<small>Only seen by Administration</small>)</h1>";
        public $html_hr             = '<HR>';
        public $html_slash          = ' / ';
        public $html_p              = '<p></p>';  
        public $html_hint_autoplay  = '<p>Play one video and the rest will auto-play thereafter</p>';  
        public $html_searchbox = "<input name='term' style='padding:10px; width: 80vw' maxlength=200 placeholder='Enter something to search the list'>";
		        
        // actions
        public $html_actions_lodges = "<a href='?view=lodges&action=details&id={lodgeid}'>Details</a> /
                                        <a href='?view=lodges&action=edit&id={lodgeid}'>Edit</a> /
						                <a href='#' onclick='return confirmDelete({lodgeid})'>Delete</a>";

        public $html_actions_reservations = "<a href='?view=reservations&action=edit&id={reservationid}'>Edit</a> /
						                <a href='#' onclick='return confirmDelete({reservationid})'>Delete</a>";
						                
        public $html_actions_clients = "<a href='?view=clients&action=details&id={clientid}'>Details</a> /
										<a href='?view=clients&action=edit&id={clientid}'>Edit</a> /
										<a href='#' onclick='return confirmDelete({clientid});'>Delete</a>";

        // font awesome icons
        public $icons_person = "<span class='fa fa-fw fa-user'></span>";
        public $icons_bank   = "<span class='fa fa-fw fa-bank'></span>";
        public $icons_list   = "<span class='fa fa-fw fa-list'></span>";
        public $icons_play   = "<span class='fa fa-fw fa-play'></span>";
        public $icons_link   = "<span class='fa fa-fw fa-link'></span>";
        public $icons_unlink = "<span class='fa fa-fw fa-unlink'></span>";
        public $icons_video  = "<span class='fa fa-fw fa-video'></span>";

		// SQL
        public $sql_getlodges  = "SELECT 
										lodge_id,
										l.name,
										t.name AS town,
										l.telephone,
										l.email,
										l.address,
										l.active,
										l.picture,
										l.summary
									FROM
										lodges l,
										towns t
                                    WHERE
                                        l.town_id = t.town_id
									ORDER BY l.name ASC;";
																
        public $sql_getclients = "SELECT 
										*
                                    FROM clients
									ORDER BY `name` ASC;";
		
		public $sql_getreservations = "SELECT 
												r.reservation_id,
												r.entrydate,
												c.name AS client,
												l.name AS lodge,
												r.date_checkin AS `Arrive`,
												r.date_checkout AS `Leave`,
												r.number_adults As adults,
												r.number_children AS kids
                                    FROM reservations r,
                                        clients c,
                                        lodges l
                                    WHERE
                                        c.client_id = r.client_id AND
                                        l.lodge_id = r.lodge_id
                                    ORDER BY 
                                            r.reservation_id DESC;";
                									
        public $sql_getreservationsforclient = "SELECT 
                                                           * 
                                                    FROM 
                                                          `reservations` 
        											WHERE 
        											      client_id={clientid} 
        											ORDER BY reservation_id DESC;";
        											
        public $sql_getreservationsforlodge = "SELECT 
                                                           r.entrydate,
                                                           c.name as client,
                                                           l.name as lodge,
                                                           date_checkin AS `Arrive`,
                                                           date_checkout AS `Leave`,                                                           
                                                           number_adults as adults,
                                                           number_children as children
                                                    FROM 
                                                          `reservations` r,
                                                          clients c,
                                                          lodges l
        											WHERE 
        											      r.client_id = c.client_id AND
        											      l.lodge_id = r.reservation_id
        											ORDER BY reservation_id DESC;";
        											
        public $sql_getvideosforplaylist = "SELECT 
                                            	psid, 
                                            	c.video_id,
                                            	title AS `Title`,
                                            	url
											FROM 
											     videos c, 
											     playlistsvideos cc 
											WHERE 
											     cc.playlist_id={playlistid} 
											AND 
											     cc.video_id = c.video_id;";       
												 
		public $sql_getplaylistsforvideo = "SELECT 
												 c.playlist_id, 
												 c.title As `Title`
											 FROM 
												  playlists c, 
												  playlistsvideos cc 
											 WHERE 
												  cc.playlist_id=c.playlist_id
											 AND 
												  cc.video_id = {videoid};";       												 
 }
