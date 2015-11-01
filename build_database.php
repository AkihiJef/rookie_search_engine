<?php
    class MyDB extends SQLite3
    {
       function __construct()
       {
          $this->open('crawler.db');
       }
    }
    $db = new MyDB();
    $sql_delete_table =<<<EOF
      DROP TABLE WEBPAGE
EOF;
    $sql_create_table =<<<EOF
      CREATE TABLE WEBPAGE
      (
       ID 			INT 	                NOT NULL,
       URL 			TEXT 	PRIMARY KEY 	NOT NULL,
       TITLE 		TEXT 					NOT NULL,
       KEYWORDS 	TEXT
      );
EOF;
	$db->exec($sql_delete_table);
    $ret = $db->exec($sql_create_table);
    $db->close();
?>