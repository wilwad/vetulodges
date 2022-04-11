<?php
/**
* MySQL Database CRUD Handler
*
* DDL: Create Table Drop Table Alter Table Add Column Modify Column
* DML: Create Read Update Delete
*
* Abstract: OOP CRUD handler
* Author:   William Sengdara (Khomasdal, Namibia) -- https://github.com/wilwad
* Date:     13 February 2022
*/
class CRUD {

   protected $db;
   
   // constructor
   public function __construct($host, $user, $pwd, $database){
            $this->db = new mysqli($host, 
                                $user, 
                                $pwd, 
                                $database);
            if ($this->db->connect_errno){
                throw new Error($this->db->connect_error);
            }
   }
   // Create part of CRUD
   // @param      string table -- name of target table to execute operation against
   //             values -- array -- associative array of col-data values
   // return:     insertid
   //
   // test: insert into table clients data name=John Smith, email=johnsmith@mail.com
   //      result = $mydb->create('clients', ['name'=>'John Smith', email=>'johnsmith@mail.com']);
   //      result = result['ok'], result['error'], result['data'] if any
   //
   public function create($table, $values){
            $cols_ = array_keys($values); $vals_ = array_values($values);
            
            $cols = []; $vals = [];
            
            foreach ($cols_ as $idx=>$col){$cols[] = "`$col`";}
            foreach ($vals_ as $idx=>$val){
                $val = trim(addslashes($val));
                $vals[] = "'$val'";
            }        
            $cols = implode(',',$cols); 
            $vals = implode(',',$vals);

            $sql = "INSERT INTO `$table`($cols) VALUES($vals)";
            //die( $sql );
            if (!$this->db->query($sql)) return $this->makeResult(false,$sql. ' -- '.$this->db->error);
            
            $insertid = $this->db->insert_id;
            
            return $this->makeResult (true,'', ['insertid'=>$insertid]);
   }

   // insert multiple rows into a table
   // parameters:
   //               table  -- string -- target table
   //               cols   -- array  -- array of column 
   //               values -- array  -- array of values in order of cols 
   // return:
   //               result -- insertid
   // insert into `table`(col1,col2) values (v1,v2),(v3,v4),(v5,v6);
   // $data = [['person1','18','0812', 'F'],
   //          ['person2','22','0813', 'M']];
   // $ret = $mydb->createMultiple('girls',['name', 'age', 'cellphone', 'sex'], $data);
   //
   public function createMultiple($table, $cols, $values){
            $recs = [];
            $cols_ = [];
            foreach($cols as $col){
                    $cols_[$col] = "`$col`";
            }
            $cols = implode(',',$cols_); 
            
            foreach ($values as $idx=>$vals){
                $vs = [];
                foreach($vals as $k=>$v){
                    $v = trim(addslashes($v));
                    $vs[] = "'$v'";
                }
                $vs = implode(',',$vs);
                $recs[] = "($vs)";
            }        
            
            $recs = implode(',', $recs);
            $sql = "INSERT INTO `$table`($cols) VALUES $recs;";
            if (!$this->db->query($sql)) return $this->makeResult(false,$this->db->error);
            
            $insertid = $this->db->insert_id;
            
            return $this->makeResult (true,'', ['insertid'=>$insertid]);
    }

   // Read part of CRUD
   // parameters: 
   //             table -- string -- name of target table to execute operation against
   // optional:
   //             col   -- string -- name of column to match against in operation
   //             id    -- any -- value to match in col [1 or 'yes']
   //             limit -- integer -- total number of rows to return
   // return:      
   //             multiple rows of data
   //             result[data] = [0:[col->val, col->val], 1: [col->val, col->val]]
   //
   // test: select row in clients where name=John Smith
   //       result = $mydb->read('clients', 'name', 'John Smith')
   //
   public function read($table, $col='', $id=0, $limitMax=100, $limitStart=0){
            $limit = $limitMax ? " LIMIT $limitStart, $limitMax": ''; // set limit or none

            $sql = "SELECT * FROM `$table` $limit;";

            if ($col && $id){
                $sql = "SELECT * FROM `$table` WHERE `$col`='$id' $limit;";
            }

            if (!($ret = $this->db->query($sql))) return $this->makeResult(false,$this->db->error);
            
            if (!$ret->num_rows){
                return $this->makeResult (false,'0 rows');
            }
            
            $data = []; $cols = [];
            
            while ($col = $ret->fetch_field()){
                $cols[] = $col->name;
            }

            while ($row = $ret->fetch_array()){
                $temp = [];
                
                foreach($cols as $col){
                    $temp[$col] = $row[$col];
                }
                
                $data[] = $temp;
            }
            
            return $this->makeResult (true,'', ['cols'=>$cols,
                                                'rows'=>$data,
                                                'total_rows'=>sizeof($data),
                                                'total_cols'=>sizeof($cols)
                                            ]);
   }
   // extra Read part of CRUD
   // parameters:
   //             sql -- string -- any sql statement
   // return:     result
   // test: fetch rows from table animals where type=bird or fish
   // $result = $mydb->readSQL("SELECT * FROM `animals` WHERE type in ('bird', 'fish') LIMIT 2,10;")
   //
   public function readSQL($sql){
            if (!($ret = $this->db->query($sql))) return $this->makeResult(false,$this->db->error);
            
            if (!$ret->num_rows){
                return $this->makeResult (false);
            }
            
            $data = []; $cols = [];
            
            while ($col = $ret->fetch_field()){
                $cols[] = $col->name;
            }

            while ($row = $ret->fetch_array()){
                $temp = [];
                
                foreach($cols as $col){
                    $temp[$col] = $row[$col];
                }
                
                $data[] = $temp;
            }
            
            return $this->makeResult (true,'', ['cols'=>$cols,
                                                'rows'=>$data,
                                                'total_rows'=>sizeof($data),
                                                'total_cols'=>sizeof($cols)
                                            ]);
   }
   // update part of CRUD
   // parameters:
   //             table  -- string -- name of target table to execute operation against
   //             values -- key-value pairs
   // optional:
   //             col    -- string -- column to match in table
   //             id     -- any -- id to match in col
   //
   // test: 
   //             update table clients, set email to sylvia@mail.com where id=22
   //             $result = $mydb->update('clients', ['email'=>'sylvia@mail.com'],'',22)
   //
   //             -- or update all columns named verified in table clients to 1
   //             $result = $mydb->update('clients', ['verified'=>1])
   //
   public function update($table, $values, $col='', $id=0){
            $vals = [];
            
            foreach($values as $key=>$val){
                $val = addslashes(trim($val));            
                $vals[] = "`$key`='$val'";
            }
            $vals = implode(',',$vals);
            
            if ($col && $id){
                $sql = "UPDATE `$table` SET $vals WHERE `$col`='$id';";
            } else {
                $sql = "UPDATE `$table` SET $vals;";
            }

            if (!$this->db->query ($sql)) return $this->makeResult (false,$this->db->error);

            return $this->makeResult (true);
   }
   
   // delete part of CRUD
   // parameters:
   //               table -- string -- name of the target table to operate on
   // optional:
   //               col   -- string -- column to match in table
   //               id    --  any -- id to match in col
   //
   // test: delete all rows from table animals
   // $result = $mydb->delete('animals')
   //
   // -- or delete rows from animals where type = bird
   // $result = $mydb->delete('animals', 'type', 'bird');
   //
   public function delete($table, $col='', $id=0){
            $sql = "DELETE FROM `$table`;";
            if ($col && $id){
                $sql = "DELETE FROM `$table` WHERE `$col`='$id';";
            }        
            
            if (!$this->db->query($sql)) return $this->makeResult(false,$this->db->error);
            $affectedrows = $this->db->affected_rows;

            return $this->makeResult (true, '', ['affectedrows'=>$affectedrows]);
   }
   
   // Utilities:
   //
   // select id, name FROM genders
   // returns id-value pair - good for html selection, option box
   public function getTableColumnData($table, $col, $idfield='id'){
            $sql = "SELECT `$idfield`, `$col` FROM `$table`";
            $this->db->query($sql) or die($this->db->error);
            if (!$ret = $this->db->query($sql)) return $this->makeResult(false,$this->db->error);
            if (!$ret->num_rows){
                return $this->makeResult(false, 'No matching record');
            }
            $data = [];
            while ($row = $ret->fetch_array()){
                $id = $row[$idfield];
                $val= $row[$col];
                
                $data[$id] = $val;
            }
            return $this->makeResult(true,'',$data);    		
    } 
    
    // returns metadata of a table
    // good for dynamic form generation where you want to have fields 
    // that correspond to column type e.g. if col type is INT, <input type='number' id='' name=''>
    // parameters:
    //              table -- string -- name of the target table
    // returns:
    //              ['id':[comment, null, type], name: [comment, type, null],...]
    public function getTableMetadata($table){
            $sql = "SHOW FULL COLUMNS FROM `$table`;";
            $this->db->query($sql) or die($this->db->error);
            if (!$ret = $this->db->query($sql)) return $this->makeResult(false,$this->db->error);
            if (!$ret->num_rows){
                return $this->makeResult(false, 'No data');
            }
            $data = [];
            while ($row = $ret->fetch_array()){
                $field   = $row['Field'];
                $type    = $row['Type'];
                $collation = $row['Collation'];
                $null    = $row['Null'];
                $key     = @$row['key'];
                $default = @$row['default'];
                $comment = $row['Comment'];

                $data[$field] = [
                                'field'=>$field,
                                'type'=>$type,
                                'collation'=>$collation,
                                'key'=>$key,
                                'default'=>$default,
                                'null'=>$null,
                                'comment'=>$comment,
                                ];
            }
            return $this->makeResult(true,'',$data);    		
    }

   // helper function: package a result array
   // parameters:
   //             result -- true | false
   //             error  -- string
   //             data   --  key/value pair
   static function makeResult($result, $error='', $data=[], $sql = ''){
        return ['ok'=>$result, 'error'=>$error, 'data'=>$data, 'sql'=>$sql];
   }
   
   // numbers from string
   function numbersFromString( $string ){
        return (int)filter_var($string, FILTER_SANITIZE_NUMBER_INT);
   }
   
   function __destruct(){
        @ $this->db->close();
   }
 }
