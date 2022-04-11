<?php
/**
* Form builder for MySQL
*
* Abstract: Generate a data entry/update form from a MySQL query
* Author:   William Sengdara (Khomasdal, Namibia) -- https://github.com/wilwad
* Date:     14 February 2022
*/
 class CForm extends CRUD {
    public function getTableMetadata($table){
        return parent::getTableMetadata($table);
    }
    public function generateForm($table, $col='', $id=0, $ignored = []){
        $sql = "SHOW FULL COLUMNS FROM `$table`;";
        $this->db->query($sql) or die($this->db->error);
        if (!$ret = $this->db->query($sql)) return $this->makeResult(false,$this->db->error);

        if ($col && $id){
        
        	$data = [];
        	
            $sql = "SELECT * FROM `$table` WHERE `$col`='$id';";
            if (!$r = $this->db->query($sql)) return $this->makeResult(false,$this->db->error);
            if (!$r->num_rows){
                return $this->makeResult(false, 'No matching record');
            }
            $cols = [];
            while ($col = $r->fetch_field()){
            	$name = $col->name;
            	if (in_array($name, $ignored)) continue;
                $cols[] = $name;
            }

            while ($row = $r->fetch_array()){
                foreach ($cols as $col){
                    $data[$col] = $row[$col] ;
                }
            }
            return $this->makeResult(true,'',$data);
        }
        
        $check = ['Field', 'Type', 'Null', 'Comment'];

        $d = [];
        while ($row = $ret->fetch_array()){
            $field   = $row['Field'];            
            if (in_array($field, $ignored)) continue;
            $d[$field] = '';
        }

        return $this->makeResult(true,'',$d);
   } 
 }
