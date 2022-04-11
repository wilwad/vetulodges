 <?php
  require('classes/settings.php');
  require('classes/CRUD.php');
  require('classes/CForm.php');  
  require('classes/App.php');
  
  $settings = new settings();
  
  if ( $settings->showPHPerrors ){
    ini_set('display_startup_errors',1);
    ini_set('display_errors',1);
    error_reporting(-1);  
  }  

  $app = new App( $settings );  
 ?>
 <!DOCTYPE html>
 <html lang='en'>
  <head>
   <meta charset='utf-8'>
   <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />   
   <title><?php echo $settings->title; ?></title>
   
   <!-- font-awesome icons -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css" integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
   <link rel='stylesheet' href='css/style.css?t=1'>
   <style>
       .menu {
           text-align: center;
       }
       
       .menu a {
           margin: 0 10px;
       }
   </style>
  </head>
  <body>
  
   <?php
    echo "<div class='margin10'>";
    echo "<div class='menu'>";
    echo $settings->buttons_home;
    echo '&nbsp;';
    echo $settings->buttons_lodges;
    echo '&nbsp;';
    echo $settings->buttons_reservations;
    echo '&nbsp;';
    echo $settings->buttons_clients;
    echo "</div>";
    //echo $settings->html_author;
    echo '</div>';

    /* using @ in case these parameters are not set */
    $view     = @ $_GET['view'];
    $action   = @ $_GET['action'];
    $actionid = (int) @ $_GET['id'];

    switch ($view){
        case 'clients':
        case 'lodges':
        case 'reservations':
          require("views/$view.php");
          break;
			
        default:
          // default or when no view is set
          require('views/home.php');
    }
   ?>
    <script src="js/main.js" defer></script>
  </body>
 </html>
