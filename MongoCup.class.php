<?php

  class MongoCup {
    
    private $conn;
    private $db;
    
    public function __construct($connectionURL = "") {
      $this->conn = new Mongo($connectionURL);
      
      $connectionURLList = explode("/", $connectionURL);
      $this->db = $this->conn->selectDB(end($connectionURLList));
    }
    
    public function put($obj, $classNameOrObj = null) {
      if(is_null($classNameOrObj))
        $this->_getCollection($obj)->save($obj);
      else
        $this->_getCollection($classNameOrObj)->save($obj);
    }
    
    public function putAll($listOfObj, $classNameOrObj = null) {
      
      if(!is_array($listOfObj))
        throw new MongoCupException("putAll: an array is required!");
      
      foreach($listOfObj as $singleObj) {
        if(is_null($classNameOrObj))
          $this->_getCollection($listOfObj[0])->save($singleObj);
        else
          $this->_getCollection($classNameOrObj)->save($singleObj);
      }
    }
        
    public function get() {
      if(func_num_args() == 3)
        return $this->_getSimpleFilter (func_get_arg (0), func_get_arg (1), func_get_arg (2));
      else if(func_num_args() == 2)
        return $this->_getFromQuery (func_get_arg (0), func_get_arg (1));
      else if(func_num_args() == 1)
        return $this->_getFromClass (func_get_arg (0));
      
      throw new MongoCupException("get() can be invoked with one, two or three params only");
    }
    
    private function _getSimpleFilter($fieldName, $fieldValue, $classNameOrObj) {
      // like getList but limit to 1
      return $this->getList($fieldName, $fieldValue, $classNameOrObj)->limit(1)->getNext();
    }
    
    private function _getFromQuery($query, $classNameOrObj) {
      // like getList but limit to 1
      return $this->getList($query, $classNameOrObj)->limit(1)->getNext();
    }
    
    private function _getFromClass($classNameOrObj) {
      
      // like getList but limit to 1
      return $this->getList($classNameOrObj)->limit(1)->getNext();
    }
    
    public function getList() {
      
      if(func_num_args() == 3)
        return $this->_getListSimpleFilter (func_get_arg (0), func_get_arg (1), func_get_arg (2));
      else if(func_num_args() == 2)
        return $this->_getListFromQuery (func_get_arg (0), func_get_arg (1));
      else if(func_num_args() == 1)
        return $this->_getListFromClass (func_get_arg (0));
      
      throw new MongoCupException("MongoDataService::getList() => can be invoked with one, two or three params only");
    }
    
    private function _getListSimpleFilter($fieldName, $fieldValue, $classNameOrObj) {
      if(!is_string($fieldName))
        throw new MongoCupException("The fieldName parameter should be a string");
      
      if(!is_string($classNameOrObj) && !is_object($classNameOrObj))
        throw new MongoCupException("The className parameter should be a string or an object instance");
      
      $mongoQuery = new MongoCupQuery();
      $mongoQuery->filter($fieldName, $fieldValue);
      
      return $this->getList($mongoQuery, $classNameOrObj);
    }
    
    private function _getListFromQuery($mongoQuery, $classNameOrObj) {
      if(get_class($mongoQuery) != "MongoCupQuery")
        throw new MongoCupException("The query parameter should be an instance of MongoCupQuery");
      
      if(!is_string($classNameOrObj) && !is_object($classNameOrObj))
        throw new MongoCupException("The className parameter should be a string or an object instance");
      
      return $this->_toMongoCursor($this->_getCollection($classNameOrObj), $mongoQuery);
    }
    
    private function _getListFromClass($classNameOrObj) {
      
      // empty query, select all
      $mongoQuery = new MongoCupQuery();
      return $this->getList($mongoQuery, $classNameOrObj);
    }
    
    public function delete($obj, $classNameOrObj) {
      if(is_null($classNameOrObj))
        return $this->_getCollection($obj)->remove($obj);
      else
        return $this->_getCollection($classNameOrObj)->remove($obj);
    }
    
    public function deleteAll() {
      
      if(func_num_args() == 2)
        return $this->_deleteAllFromQuery (func_get_arg (0), func_get_arg (1));
      else if(func_num_args() == 1)
        if(is_array(func_get_arg(0)))
          return $this->_deleteAllIterable (func_get_arg (0));
        else if(is_string(func_get_arg(0)))
          return $this->_deleteAllFromClass (func_get_arg (0));
      
      throw new MongoCupException("MongoDataService::getList() => Unrecognized function invokation pattern");
    }
    
    private function _deleteAllIterable($listOfObj) {
      foreach($listOfObj as $obj)
        $this->delete ($obj);
    }
    
    private function _deleteAllFromQuery($query, $classNameOrObj) {
      
      if(get_class($query) != "MongoCupQuery")
        throw new MongoCupException("The query parameter should be an instance of MongoCupQuery");
      
      if(!is_string($classNameOrObj) && !is_object($classNameOrObj))
        throw new MongoCupException("The className parameter should be a string or an object instance");
      
      $deleteArray = $this->_toDeleteArray($query);
      
      return $this->_getCollection($classNameOrObj)->remove($deleteArray);
    }
    
    private function _deleteAllFromClass($classNameOrObj) {
      
      if(!is_string($classNameOrObj) && !is_object($classNameOrObj))
        throw new MongoCupException("The className parameter should be a string or an object instance");
      
      return $this->_getCollection($classNameOrObj)->remove(array());
    }
    
    public function getResultSetSize() {
      
      if(func_num_args() == 3)
        return $this->_getResultSetSimpleFilter (func_get_arg (0), func_get_arg (1), func_get_arg (2));
      else if(func_num_args() == 2)
        return $this->_getResultSetSizeFromQuery (func_get_arg (0), func_get_arg (1));
      else if(func_num_args() == 1)
        return $this->_getResultSetSizeFromClass (func_get_arg (0));
      
      throw new MongoCupException("MongoDataService::getResultSetSize() => can be invoked with one, two or three params only");
    }
    
    private function _getResultSetSimpleFilter($fieldName, $fieldValue, $classNameOrObj) {
      
      if(!is_string($fieldName))
        throw new MongoCupException("The fieldName parameter should be a string");
      
      if(!is_string($classNameOrObj) && !is_object($classNameOrObj))
        throw new MongoCupException("The className parameter should be a string or an object instance");
      
      return $this->_getListSimpleFilter($fieldName, $fieldValue, $classNameOrObj)->count();
    }
    
    private function _getResultSetSizeFromQuery($query, $classNameOrObj) {
      
      if(get_class($query) != "MongoCupQuery")
        throw new MongoCupException("The query parameter should be an instance of MongoCupQuery");
      
      if(!is_string($classNameOrObj) && !is_object($classNameOrObj))
        throw new MongoCupException("The className parameter should be a string or an object instance");
      
      return $this->_getListFromQuery($query, $classNameOrObj)->count();
    }
    
    private function _getResultSetSizeFromClass($classNameOrObj) {
      
      if(!is_string($classNameOrObj) && !is_object($classNameOrObj))
        throw new MongoCupException("The className parameter should be a string or an object instance");
      
      return $this->_getListFromClass($classNameOrObj)->count();
    }
    
    public function ensureIndex($fieldName, $classNameOrObj) {
      
      if(!is_string($fieldName))
        throw new MongoCupException("The fieldName parameter should be a string");
      
      $this->_getCollection($classNameOrObj)->ensureIndex(array($fieldName => 1), array("background" => true));
    }
    
    public function newQuery() {
      return new MongoCupQuery();
    }
    
    private function _getCollection($objOrClassName) {
      if(is_string($objOrClassName))
        return $this->db->selectCollection($objOrClassName);
            
      $reflClass = null;
      try {
         $reflClass = new ReflectionClass($objOrClassName);
      } catch (ReflectionException $ex) {
         throw new MongoCupException("Unable to find class with name '".$objOrClassName."'");
      }
      
      $className = $reflClass->getName();
      
      return $this->db->selectCollection($className);
    }
    
    private function _toMongoCursor($cursorOrCollection, $mongoQuery) {
      
      $fieldFilterList = array();
      
      foreach($mongoQuery->getFieldFilterList() as $fieldFilter) {
        
        if($fieldFilter->getFieldFilterType() == FieldFilter::EQUAL) {
          $fieldFilterList[$fieldFilter->getFieldName()] = $fieldFilter->getFieldValue();
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::NOT_EQUAL) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$ne' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::GREATER_THAN) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$gt' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::GREATER_THAN_INC) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$gte' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::LOWER_THAN) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$lt' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::LOWER_THAN_INC) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$lte' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::IN) {
          
          if(!is_array($fieldFilter->getFieldValue()))
            $fieldFilter = new FieldFilter ($fieldFilter->getFieldName(), array($fieldFilter->getFieldValue()), $fieldFilter->getFieldFilterType());
          
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$in' => $fieldFilter->getFieldValue());
        } 
      }
      
      // we apply the field filters
      $cursorOrCollection = $cursorOrCollection->find($fieldFilterList);
         
      
      $orderFilterList = array();
      
      foreach($mongoQuery->getOrderFilterList() as $orderFilter) {
        
        if($orderFilter->getFieldFilterType() == OrderFilter::ASCENDING) {
          $orderFilterList[$fieldFilter->getFieldName()] = 1;
        } else {
          $orderFilterList[$fieldFilter->getFieldName()] = -1;
        } 
      }
      // we apply the sort filters
      $cursorOrCollection->sort($orderFilterList);
      
      // we apply the offset
      $cursorOrCollection->skip($mongoQuery->getOffset());
      
      // we apply the limit
      $cursorOrCollection->limit($mongoQuery->getLimit());
      
      return $cursorOrCollection;
  }
  
  private function _toDeleteArray($mongoQuery) {
      
      $fieldFilterList = array();
      
      foreach($mongoQuery->getFieldFilterList() as $fieldFilter) {
        
        if($fieldFilter->getFieldFilterType() == FieldFilter::EQUAL) {
          $fieldFilterList[$fieldFilter->getFieldName()] = $fieldFilter->getFieldValue();
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::NOT_EQUAL) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$ne' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::GREATER_THAN) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$gt' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::GREATER_THAN_INC) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$gte' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::LOWER_THAN) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$lt' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::LOWER_THAN_INC) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$lte' => $fieldFilter->getFieldValue());
        } else if($fieldFilter->getFieldFilterType() == FieldFilter::IN) {
          $fieldFilterList[$fieldFilter->getFieldName()] = array('$in' => $fieldFilter->getFieldValue());
        } 
      }
      
      return $fieldFilterList;
    }
  }

  class MongoCupException extends Exception {

    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
  }

  class MongoCupQuery {

    private $fieldFilterList = array();
    private $orderFilterList = array();
    private $limit = 2147483647;
    private $offset = 0;

    public static function newQuery() {
      return new MongoCupQuery();
    }

    public function filter($fieldName, $fieldValue) {

      if(!is_string($fieldName)) 
        throw new MongoCupException("filter: the fieldName parameter must be a string");

      $fieldName = trim($fieldName);

      if(strpos($fieldName, " ") === false)
        $parsedFieldName = $fieldName;
      else
        $parsedFieldName = substr ($fieldName, 0, strrpos($fieldName, " "));

      if(endsWith($fieldName, " =") || endsWith($fieldName, " =="))
        $this->fieldFilterList[] = new FieldFilter ($parsedFieldName, $fieldValue, FieldFilter::EQUAL);
      if(endsWith($fieldName, " !="))
        $this->fieldFilterList[] = new FieldFilter ($parsedFieldName, $fieldValue, FieldFilter::NOT_EQUAL);
      else if(endsWith($fieldName, " >"))
        $this->fieldFilterList[] = new FieldFilter ($parsedFieldName, $fieldValue, FieldFilter::GREATER_THAN);
      else if(endsWith($fieldName, " >="))
        $this->fieldFilterList[] = new FieldFilter ($parsedFieldName, $fieldValue, FieldFilter::GREATER_THAN_INC);
      else if(endsWith($fieldName, " <"))
        $this->fieldFilterList[] = new FieldFilter ($parsedFieldName, $fieldValue, FieldFilter::LOWER_THAN);
      else if(endsWith($fieldName, " <="))
        $this->fieldFilterList[] = new FieldFilter ($parsedFieldName, $fieldValue, FieldFilter::LOWER_THAN_INC);
      else if(endsWith($fieldName, " in"))
        $this->fieldFilterList[] = new FieldFilter ($parsedFieldName, $fieldValue, FieldFilter::IN);
      else
        $this->fieldFilterList[] = new FieldFilter ($fieldName, $fieldValue, FieldFilter::EQUAL);

      return $this;
    }

    public function orderBy($fieldName, $orderType = OrderFilter::ASCENDING) {

      if(!is_string($fieldName))
        throw new MongoCupException("orderBy: The fieldName param should be a string");

      $fieldName = trim($fieldName);

      $this->orderFilterList[] = new OrderFilter($fieldName, $orderType);

      return $this;
    }

    public function limit($limit) {
      if(!is_int($limit))
        throw new MongoCupException("The limit field should be an integer");

      if($limit < 1)
        throw new MongoCupException("The limit value can only be greater or equal to 1");

      $this->limit = $limit;

      return $this;
    }

    public function offset($offset) {

      if(!is_int($offset))
        throw new MongoCupException("The offset field should be an integer");

      if($offset < 1)
        throw new MongoCupException("The offset value can only be greater or equal to 1");

      $this->offset = $offset;

      return $this;
    }

    public function getFieldFilterList() { return $this->fieldFilterList; } 
    public function getOrderFilterList() { return $this->orderFilterList; } 
    public function getLimit() { return $this->limit; }
    public function getOffset() { return $this->offset; }
  }

  class OrderFilter {
    
    const ASCENDING = "ascending";
    const DESCENDING = "descending";
    
    private $fieldName;
    private $orderType;
    
    public function __construct($fieldName, $orderType) {
      
      if(!is_string($fieldName))
        throw new MongoQueryException("The fieldName parameter should be a string");
      
      if(!is_string($orderType))
        throw new MongoQueryException("The orderType parameter should be a string");
      
      if($orderType != self::ASCENDING && $orderType != self::DESCENDING)
        throw new MongoQueryException("The orderType field can only be ASCENDING or DESCENDING");
      
      $this->fieldName = $fieldName;
      
      $this->orderType = $orderType;
    }
    
    public function getFieldName() { return $this->fieldName; }
    public function getOrderType() { return $this->orderType; }
  }
  
  class FieldFilter {
    
    private $fieldName;
    private $fieldValue;
    private $fieldFilterType;

    const EQUAL = "equal";
    const NOT_EQUAL = "notEqual";
    const LOWER_THAN_INC = "lowerThanInc";
    const LOWER_THAN = "lowerThan";
    const GREATER_THAN_INC = "greaterThanInc";
    const GREATER_THAN = "greaterThan";
    const IN = "in";

    public function __construct($fieldName, $fieldValue, $fieldFilterType) {

      if($fieldFilterType != self::EQUAL && 
              $fieldFilterType != self::LOWER_THAN_INC &&
              $fieldFilterType != self::LOWER_THAN &&
              $fieldFilterType != self::GREATER_THAN_INC &&
              $fieldFilterType != self::GREATER_THAN &&
              $fieldFilterType != self::IN &&
              $fieldFilterType != self::NOT_EQUAL)
        throw new MongoQueryException("The fieldFilterType value can only be: EQUAL, NOT_EQUAL, LOWER_THAN_INC, LOWER_THAN, GREATER_THAN_INC, GREATER_THAN, IN");

      $this->fieldName = $fieldName;
      $this->fieldValue = $fieldValue;
      $this->fieldFilterType = $fieldFilterType;
    }

    public function getFieldName() { return $this->fieldName; }
    public function getFieldValue() { return $this->fieldValue; }
    public function getFieldFilterType() { return $this->fieldFilterType; }
  }
  
  function startsWith($haystack, $needle)
  {
      $length = strlen($needle);
      return (substr($haystack, 0, $length) === $needle);
  }

  function endsWith($haystack, $needle)
  {
      $length = strlen($needle);
      if ($length == 0) {
          return true;
      }

      return (substr($haystack, -$length) === $needle);
  }
  
?>