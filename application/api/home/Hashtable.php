<?php
namespace app\api\home;

class Hashtable  {
    private $_hashKey;
    private $_hashSource;
    private $_tableName;
    private $_hashKeyBit;
    private $_separator;

    /*
     * append user id to generate hash key
     * @return $_key string
     */
    private function generateHashKey() {
         $_key = substr($this->_hashSource, strlen($this->_hashSource) - $this->_hashKeyBit);
         return $_key;
    }

    /*
     * generate hash table name
     * @return $_hashTableName string
     */
    public function getHashTableName($_tableName, $_hashSource, $_hashKeyBit = 1, $_separator = "_") {
        $this->setTableName($_tableName);
        $this->setHashSource($_hashSource);
        $this->setHashKeyBit($_hashKeyBit);
        $this->setSeparator($_separator);
        $this->setHashKey($this->generateHashKey());
        $_hashTableName = $this->_tableName . $this->_separator .$this->_hashKey;
        return $_hashTableName;
    }

    public function setTableName ($_tableName) {
        $this->_tableName = $_tableName;
    }

    public function setHashKeyBit ($_hashKeyBit) {
        $this->_hashKeyBit = $_hashKeyBit;
    }

    public function setSeparator ($_separator) {
        $this->_separator = $_separator;
    }

    public function setHashKey ($_hashKey) {
        $this->_hashKey = $_hashKey;
    }

    public function setHashSource ($_hashSource) {
        $this->_hashSource = $_hashSource;
    }
}