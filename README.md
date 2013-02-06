MongoCup
=========

A simple tiny OO php wrapper for MongoDB

How to use it

    // We create a connection to the database
    $mongoCup = new MongoCup("mongodb://localhost:27017/myDbName");

    // We create an object
    $myObj = new MyObject();
    $myObj->key = "value";

    // we start adding objects
    // mongodb adds an '_id' field behind the scenes as a primary index
    $mongoCup->put($myObj);

    // we retrieve the objects we are interested in

    // we want all objects of type 'MyObject'
    $queryCursor = $mongoCup->getList("MyObject");
    // or
    $queryCursor = $mongoCup->getList(new MyObject());

    // we want objects with a with a specific field value
    $queryCursor = $mongoCup->getList('key', "something", "MyObject");
    // which is equivalent to
    $queryCursor = $mongoCup->getList('key =', "something", "MyObject");
    // or
    $queryCursor = $mongoCup->getList('key ==', "something", "MyObject");

    // but we could try
    $queryCursor = $mongoCup->getList('key !=', "something", "MyObject");
    $queryCursor = $mongoCup->getList('key >', "something", "MyObject");
    $queryCursor = $mongoCup->getList('key >=', "something", "MyObject");
    $queryCursor = $mongoCup->getList('key <=', "something", "MyObject");
    $queryCursor = $mongoCup->getList('key <', "something", "MyObject");

    // or even
    $queryCursor = $mongoCup->getList('key in', 'arrayValue', "MyObject");
    // similar to
    $queryCursor = $mongoCup->getList('key in', array('arrayValue'), "MyObject");

    // for more generic queries with sorting
    $queryCursor = $mongoCup->getList(MongoCupQuery::newQuery()
          .filter('key', 'value')
          .filter('anotherKey <', 1234)
          .orderBy('orderKey') // defaults to ascending
          .limit(10)
          .skip(1),"MyObject");

    // or with descending ordering
    $queryCursor = $mongoCup->getList(MongoCupQuery::newQuery()
          .filter('key', 'value')
          .filter('anotherKey <', 1234)
          .orderBy('orderKey', OrderFilter::DESCENDING)
          .limit(10)
          .skip(1),"MyObject");


    // to delete an object (based on the '_id' field)
    $mongoCup->delete($obj);

    // to delete multiple objects (based on the '_id' field)
    $mongoCup->deleteAll($objList);

    // to delete based on a query
    $mongoCup->deleteAll(MongoCupQuery::newQuery().filter('key', 'value'), "MyObject");

    // to drop all objects of a class
    $mongoCup->deleteAll("MyObject");

    // to do count() queries
    $mongoCup->getResultSetSize("key", "value", "MyObject");
    $mongoCup->getResultSetSize(MongoCupQuery::newQuery().filter('key', 'value'), "MyObject");
    $mongoCup->getResultSetSize("MyObject");

    // to ensure indexes
    $mongoCup->ensureIndex("fieldName", "MyObject");

Notes
--------------

 - All query calls return a query cursor. The actual data is fetched when the cursor is iterated through.
 -- No support for the batchSize param yet
 - All calls throw MongoLiteException in case of syntax error and/or malformed params
 - No support for FSYNC yet
 - No support batch size yet
 - Each object is stored in a collection named after the object name (no namespace support yet)
 - The constructor accepts a connection URL in the form mongodb://username:password@host:port/dbName
 -- The dbName parameter must be specified
 -- The username and password parameters are optional and depend on the database configuration
