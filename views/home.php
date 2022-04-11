<style>
.banner-text {
    display: block;
    text-align: center;
    vertical-align: middle;
    line-height: 500px;
    font-size: 5em;
    color: #FFF;
    text-shadow: 2px 2px #666464;
}
</style>
<?php
 $banner = "images/namibia-erongo-wilderness-camp-views.jpg";
 $title  = $settings->title;
 echo "<section style='width:100%; height: 500px; 
    background: url($banner) no-repeat fixed; background-size:cover'>
        <span class='banner-text'>$title</span>
       </section>";
       
 $ret = $app->getLodges();
 echo $settings->html_p;
 if (!$ret['ok']){
    echo $settings->error_nolodges;
 } else {
    echo $settings->html_cta_lodges;
    echo $settings->html_p;
    
    echo "<div class='margin10'>";
    foreach($ret['data']['rows'] as $row){
        $id   = $row['lodge_id'];
        $name = $row['name'];
        $town = $row['town'];
        $tel = $row['telephone'];
        $email = $row['email'];
        $picture = $row['picture'];
        $summary = $row['summary'];
        if (!file_exists($picture)) $picture = 'images/placeholder.webp';
        
        echo "<div class='card'>
                <!-- img src='$picture' alt='Image' style='width:100%'-->
                <div style='width:100%;height:200px; background: url($picture) no-repeat; background-size: cover;'>
                </div>
                <div class='container'>
                 <h4><b>$name <small>in</small> $town</b></h4>
                 <small>$summary</small>
                 <p>$tel / $email </p>
                 <p>
                  <a href='?view=reservations&action=booknow&id=$id'>Book Now</a> &middot;
                  <a href='?view=lodges&action=details&id=$id'>Details</a>
                 </p>
                </div>
              </div>";
    }
    echo '</div>';
    echo "<div style='clear:both'></div>";
 }
 
 $banner = 'images/eeff6d785acacb8312e82bb3b635c202.jpg';
  echo "<section style='width:100%; height: 500px; 
    background: url($banner) no-repeat fixed; background-size:cover'>
       </section>";
?>
