<?php 
 /**
  * Class specific to a specific process
  */
 class App {
    private $settings;
    public $crud;  // database CRUD operations
    
    // class constructor 
    public function __construct($appsettings){
            $this->crud = new CRUD( $appsettings->database_host, 
                                    $appsettings->database_user, 
                                    $appsettings->database_pwd, 
                                    $appsettings->database_name );
                                    
            $this->settings = $appsettings;
    }
    
    function randomPassword($length=6) {
            $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
            $temp = "";

            for ($i = 0; $i < 30; $i++) {
            $random_int = mt_rand();
            $temp .= $alphabet[$random_int % strlen($alphabet)];
            }

            return substr($temp,1,$length);
    }

    // handling lodges
    public function getLodge($id){
        return $this->crud->read($this->settings->tables_lodges, 'lodge_id', $id);
    }    
    public function getLodges(){
            return $this->crud->readSQL($this->settings->sql_getlodges);
    }
    public function createLodge($postdata){
            return $this->crud->create( $this->settings->tables_lodges, $postdata);
    }
    public function updateLodge($id, $postdata){
            return $this->crud->update( $this->settings->tables_lodges, $postdata, 'lodge_id', $id);
    }
    public function deleteLodge($id){
            return $this->crud->delete($this->settings->tables_lodges, 'lodge_id', $id);
    }
    // reservations
    public function getReservations(){
            return $this->crud->readSQL($this->settings->sql_getreservations);
    }
    public function createReservation($postdata){
            return $this->crud->create( $this->settings->tables_reservations, $postdata);
    }
    public function updateReservation($id, $postdata){
            return $this->crud->update( $this->settings->tables_reservations, $postdata, 'reservation_id', $id);
    }
    public function deleteReservation($id){
            return $this->crud->delete($this->settings->tables_reservations, 'reservation_id', $id);
    }    
    public function getReservationsForClient($id){
            $sql = $this->settings->sql_getreservationsforclient;
            $sql = str_replace('{clientid}', $id, $sql);             
            return $this->crud->readSQL($sql);
    }
    public function getReservationsForLodge($id){
        $sql = str_replace('{lodgeid}', $id, $this->settings->sql_getreservationsforlodge);           
        return $this->crud->readSQL($sql);   
    }

    // handling clients
    public function getClients(){
            return $this->crud->readSQL($this->settings->sql_getclients);
    }
    public function createClient($postdata){
            return $this->crud->create( $this->settings->tables_clients, $postdata);
    }
    public function updateClient($id, $postdata){
            return $this->crud->update( $this->settings->tables_clients, $postdata, 'client_id', $id);
    }
    public function deleteClient($id){
            return $this->crud->delete($this->settings->tables_playlists, 'client_id', $id);
    }
        
 } // class App end 