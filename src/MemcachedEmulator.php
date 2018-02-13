<?php

/**
 * Memcached emulator class.
 */
class MemcachedEmulator
{
    /**
     * Predefined Constants
     *
     * @see http://php.net/manual/en/memcached.constants.php
     */

    const LIBMEMCACHED_VERSION_HEX = 16777240;
    const OPT_COMPRESSION = -1001;
    const OPT_COMPRESSION_TYPE = -1004;
    const OPT_PREFIX_KEY = -1002;
    const OPT_SERIALIZER = -1003;
    const OPT_STORE_RETRY_COUNT = -1005;

    const HAVE_IGBINARY = 0;
    const HAVE_JSON = 0;
    const HAVE_MSGPACK = 0;
    const HAVE_SESSION = 1;
    const HAVE_SASL = 1;

    const OPT_HASH = 2;
    const HASH_DEFAULT = 0;
    const HASH_MD5 = 1;
    const HASH_CRC = 2;
    const HASH_FNV1_64 = 3;
    const HASH_FNV1A_64 = 4;
    const HASH_FNV1_32 = 5;
    const HASH_FNV1A_32 = 6;
    const HASH_HSIEH = 7;
    const HASH_MURMUR = 8;

    const OPT_DISTRIBUTION = 9;
    const DISTRIBUTION_MODULA = 0;
    const DISTRIBUTION_CONSISTENT = 1;
    const DISTRIBUTION_VIRTUAL_BUCKET = 6;

    const OPT_LIBKETAMA_COMPATIBLE = 16;
    const OPT_LIBKETAMA_HASH = 17;
    const OPT_TCP_KEEPALIVE = 32;
    const OPT_BUFFER_WRITES = 10;
    const OPT_BINARY_PROTOCOL = 18;
    const OPT_NO_BLOCK = 0;
    const OPT_TCP_NODELAY = 1;
    const OPT_SOCKET_SEND_SIZE = 4;
    const OPT_SOCKET_RECV_SIZE = 5;
    const OPT_CONNECT_TIMEOUT = 14;
    const OPT_RETRY_TIMEOUT = 15;
    const OPT_DEAD_TIMEOUT = 36;
    const OPT_SEND_TIMEOUT = 19;
    const OPT_RECV_TIMEOUT = 20;
    const OPT_POLL_TIMEOUT = 8;
    const OPT_CACHE_LOOKUPS = 6;
    const OPT_SERVER_FAILURE_LIMIT = 21;
    const OPT_AUTO_EJECT_HOSTS = 28;
    const OPT_HASH_WITH_PREFIX_KEY = 25;
    const OPT_NOREPLY = 26;
    const OPT_SORT_HOSTS = 12;
    const OPT_VERIFY_KEY = 13;
    const OPT_USE_UDP = 27;
    const OPT_NUMBER_OF_REPLICAS = 29;
    const OPT_RANDOMIZE_REPLICA_READ = 30;
    const OPT_REMOVE_FAILED_SERVERS = 35;
    const OPT_SERVER_TIMEOUT_LIMIT = 37;

    const RES_SUCCESS = 0;
    const RES_FAILURE = 1;
    const RES_HOST_LOOKUP_FAILURE = 2;
    const RES_UNKNOWN_READ_FAILURE = 7;
    const RES_PROTOCOL_ERROR = 8;
    const RES_CLIENT_ERROR = 9;
    const RES_SERVER_ERROR = 10;
    const RES_WRITE_FAILURE = 5;
    const RES_DATA_EXISTS = 12;
    const RES_NOTSTORED = 14;
    const RES_NOTFOUND = 16;
    const RES_PARTIAL_READ = 18;
    const RES_SOME_ERRORS = 19;
    const RES_NO_SERVERS = 20;
    const RES_END = 21;
    const RES_ERRNO = 26;
    const RES_BUFFERED = 32;
    const RES_TIMEOUT = 31;
    const RES_BAD_KEY_PROVIDED = 33;
    const RES_STORED = 15;
    const RES_DELETED = 22;
    const RES_STAT = 24;
    const RES_ITEM = 25;
    const RES_NOT_SUPPORTED = 28;
    const RES_FETCH_NOTFINISHED = 30;
    const RES_SERVER_MARKED_DEAD = 35;
    const RES_UNKNOWN_STAT_KEY = 36;
    const RES_INVALID_HOST_PROTOCOL = 34;
    const RES_MEMORY_ALLOCATION_FAILURE = 17;
    const RES_CONNECTION_SOCKET_CREATE_FAILURE = 11;
    const RES_E2BIG = 37;
    const RES_KEY_TOO_BIG = 39;
    const RES_SERVER_TEMPORARILY_DISABLED = 47;
    const RES_SERVER_MEMORY_ALLOCATION_FAILURE = 48;
    const RES_AUTH_PROBLEM = 40;
    const RES_AUTH_FAILURE = 41;
    const RES_AUTH_CONTINUE = 42;
    const RES_PAYLOAD_FAILURE = -1001;

    const SERIALIZER_PHP = 1;
    const SERIALIZER_IGBINARY = 2;
    const SERIALIZER_JSON = 3;
    const SERIALIZER_JSON_ARRAY = 4;
    const SERIALIZER_MSGPACK = 5;

    const COMPRESSION_FASTLZ = 2;
    const COMPRESSION_ZLIB = 1;

    const GET_PRESERVE_ORDER = 1;
    const GET_ERROR_RETURN_VALUE = false;

    /** @var array Dummy option array */
    protected $options =
        [
            self::OPT_COMPRESSION          => true,
            self::OPT_SERIALIZER           => self::SERIALIZER_PHP,
            self::OPT_PREFIX_KEY           => '',
            self::OPT_HASH                 => self::HASH_DEFAULT,
            self::OPT_DISTRIBUTION         => self::DISTRIBUTION_MODULA,
            self::OPT_LIBKETAMA_COMPATIBLE => false,
            self::OPT_BUFFER_WRITES        => false,
            self::OPT_BINARY_PROTOCOL      => false,
            self::OPT_NO_BLOCK             => false,
            self::OPT_TCP_NODELAY          => false,

            // This two is a value by guess
            self::OPT_SOCKET_SEND_SIZE     => 32767,
            self::OPT_SOCKET_RECV_SIZE     => 65535,

            self::OPT_CONNECT_TIMEOUT      => 1000,
            self::OPT_RETRY_TIMEOUT        => 0,
            self::OPT_SEND_TIMEOUT         => 0,
            self::OPT_RECV_TIMEOUT         => 0,
            self::OPT_POLL_TIMEOUT         => 1000,
            self::OPT_CACHE_LOOKUPS        => false,
            self::OPT_SERVER_FAILURE_LIMIT => 0,
        ];

    /**
     * Unique instance ID.
     *
     * @var string
     */
    protected $_persistent_id;

    /**
     * Last result code.
     *
     * @var int
     */
    protected $_result_code = self::RES_SUCCESS;

    /**
     * Last result message.
     *
     * @var string
     */
    protected $_result_message = '';

    /**
     * Servers list array.
     *
     * @var array
     */
    protected $_servers = [];

    /**
     * Socket connection handle.
     *
     * @var resource
     */
    protected $_socket;

    /**
     * The key of currently used server.
     *
     * @var string
     */
    protected $_current_server_key;

    /**
     * Socket connection handles per server key.
     *
     * @var resource[]
     */
    protected $_sockets = [];

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Create a Memcached instance
     *
     * @link http://php.net/manual/en/memcached.construct.php
     * @param string $persistent_id [optional]
     */
    public function __construct($persistent_id = null)
    {
        $this->_persistent_id = $persistent_id;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Add an item under a new key
     *
     * @link http://php.net/manual/en/memcached.add.php
     * @param string $key        <p>The key under which to store the value.</p>
     * @param mixed  $value      <p>The value to store.</p>
     * @param int    $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_NOTSTORED</b> if the key already exists.
     */
    public function add($key, $value, $expiration = 0)
    {
        return $this->addByKey(null, $key, $value, $expiration);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Add an item under a new key on a specific server
     *
     * @link http://php.net/manual/en/memcached.addbykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key        <p>The key under which to store the value.</p>
     * @param mixed  $value      <p>The value to store.</p>
     * @param int    $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_NOTSTORED</b> if the key already exists.
     */
    public function addByKey($server_key, $key, $value, $expiration = 0)
    {
        $key = $this->_getKey($key);
        $value = $this->_serialize($value);
        $expiration = (int)$expiration;

        if ($this->_writeSocket($server_key, "add $key 0 $expiration " . \strlen($value))) {
            if ('STORED' === $result = $this->_writeSocket($server_key, $value, true)) {
                $this->_result_code = self::RES_SUCCESS;
                $this->_result_message = '';

                return true;
            }

            if ($result === 'NOT_STORED') {
                $this->_result_code = self::RES_NOTSTORED;
                $this->_result_message = 'Add failed, key already exists.';

                return true;
            }
        }

        $this->_result_code = self::RES_FAILURE;
        $this->_result_message = 'Add failed.';

        return false;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Add a server to the server pool
     *
     * @link http://php.net/manual/en/memcached.addserver.php
     * @param string $host   <p>The hostname of the memcache server. If the hostname is invalid, data-related
     *                       operations will set <b>Memcached::RES_HOST_LOOKUP_FAILURE</b> result code.</p>
     * @param int    $port   <p>The port on which memcache is running. Usually, this is 11211.</p>
     * @param int    $weight [optional] <p>
     *                       The weight of the server relative to the total weight of all the
     *                       servers in the pool. This controls the probability of the server being
     *                       selected for operations. This is used only with consistent distribution
     *                       option and usually corresponds to the amount of memory available to
     *                       memcache on that server.
     *                       </p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function addServer($host, $port, $weight = 0)
    {
        // Create native server key.
        $key = $host . ':' . $port;

        if (isset($this->_servers[$key])) {
            $this->_result_code = self::RES_FAILURE;
            $this->_result_message = 'Server already exists.';

            return false;
        }

        $this->_servers[$key] = [
            'host'   => $host,
            'port'   => $port,
            'weight' => $weight,
        ];

        // todo - no errors are displayed if we add invalid server details.
        // it's correct in native Memcached, but maybe we can be more informative.

        return true;
    }

    /**
     * (PECL memcached &gt;= 0.1.1)<br/>
     * Add multiple servers to the server pool
     *
     * @link http://php.net/manual/en/memcached.addservers.php
     * @param array $servers
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function addServers(array $servers)
    {
        foreach ($servers as $server) {
            if (count($server) !== 3) {
                return false;
            }

            /** @noinspection MultiAssignmentUsageInspection */
            list($host, $port, $weight) = $server;

            if (!$this->addServer($host, $port, $weight)) {
                return false;
            }
        }

        return true;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Append data to an existing item
     *
     * @link http://php.net/manual/en/memcached.append.php
     * @param string $key   <p>The key under which to store the value.</p>
     * @param string $value <p>The string to append.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                      The <b>Memcached::getResultCode</b> will return
     *                      <b>Memcached::RES_NOTSTORED</b> if the key does not exist.
     */
    public function append($key, $value)
    {
        return $this->appendByKey(null, $key, $value);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Append data to an existing item on a specific server
     *
     * @link http://php.net/manual/en/memcached.appendbykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key        <p>The key under which to store the value.</p>
     * @param string $value      <p>The string to append.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_NOTSTORED</b> if the key does not exist.
     */
    public function appendByKey($server_key, $key, $value)
    {
        // Append does not take <flags> or <exptime> parameters but you must provide them !
        // Doesn't work with enabled compression.

        $key_string = $this->_getKey($key);

        if ($this->_writeSocket($server_key, "append $key_string 0 0 " . \strlen($value))) {
            if ($this->_writeSocket($server_key, $value, true) === 'STORED') {
                $this->_result_code = self::RES_SUCCESS;
                $this->_result_message = '';

                return true;
            }

            $this->_result_code = self::RES_NOTSTORED;
            $this->_result_message = 'NOT STORED';

            return false;
        }

        $this->_result_code = self::RES_FAILURE;
        $this->_result_message = 'Append failed.';

        return false;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Compare and swap an item
     *
     * @link http://php.net/manual/en/memcached.cas.php
     * @param float  $cas_token  <p>Unique value associated with the existing item. Generated by memcache.</p>
     * @param string $key        <p>The key under which to store the value.</p>
     * @param mixed  $value      <p>The value to store.</p>
     * @param int    $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_DATA_EXISTS</b> if the item you are trying
     *                           to store has been modified since you last fetched it.
     */
    public function cas($cas_token, $key, $value, $expiration = 0)
    {
        return $this->casByKey($cas_token, null, $key, $value, $expiration);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Compare and swap an item on a specific server
     *
     * @link http://php.net/manual/en/memcached.casbykey.php
     * @param float  $cas_token  <p>Unique value associated with the existing item. Generated by memcache.</p>
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key        <p>The key under which to store the value.</p>
     * @param mixed  $value      <p>The value to store.</p>
     * @param int    $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_DATA_EXISTS</b> if the item you are trying
     *                           to store has been modified since you last fetched it.
     */
    public function casByKey($cas_token, $server_key, $key, $value, $expiration = 0)
    {
        $key_string = $this->_getKey($key);
        $value_string = $this->_serialize($value);
        $expiration = (int)$expiration;

        if ($this->_writeSocket($server_key,
            "cas $key_string 0 $expiration " . \strlen($value_string) . ' ' . \addslashes($cas_token))) {
            if ($this->_writeSocket($server_key, $value_string, true) === 'STORED') {
                $this->_result_code = self::RES_SUCCESS;
                $this->_result_message = '';

                return true;
            }

            $this->_result_code = self::RES_NOTSTORED;
            $this->_result_message = 'Cas failed.';

            return false;
        }

        $this->_result_code = self::RES_FAILURE;
        $this->_result_message = 'Cas failed.';

        return false;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Decrement numeric item's value
     *
     * @link http://php.net/manual/en/memcached.decrement.php
     * @param string $key           <p>The key of the item to decrement.</p>
     * @param int    $offset        [optional] <p>The amount by which to decrement the item's value.</p>
     * @param int    $initial_value [optional] <p>The value to set the item to if it doesn't currently exist.</p>
     * @param int    $expiry        [optional] <p>The expiry time to set on the item.</p>
     * @return int item's new value on success or <b>FALSE</b> on failure.
     */
    public function decrement($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        // Note: we use only single default server.
        return $this->decrementByKey(null, $key, $offset, $initial_value, $expiry);
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Decrement numeric item's value, stored on a specific server
     *
     * @link http://php.net/manual/en/memcached.decrementbykey.php
     * @param string $server_key    <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key           <p>The key of the item to decrement.</p>
     * @param int    $offset        [optional] <p>The amount by which to decrement the item's value.</p>
     * @param int    $initial_value [optional] <p>The value to set the item to if it doesn't currently exist.</p>
     * @param int    $expiry        [optional] <p>The expiry time to set on the item.</p>
     * @return int item's new value on success or <b>FALSE</b> on failure.
     */
    public function decrementByKey($server_key, $key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        // todo - switch to native socket operation.
        if (false === $value = $this->getByKey($server_key, $key)) {
            $value = $initial_value;
        }

        $value -= $offset;

        // If the operation would decrease the value below 0, the new value will be 0.
        $value = \max(0, $value);

        return $this->setByKey($server_key, $key, $value, $expiry) ?: false;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Delete an item
     *
     * @link http://php.net/manual/en/memcached.delete.php
     * @param string $key  <p>The key to be deleted.</p>
     * @param int    $time [optional] <p>The amount of time the server will wait to delete the item.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                     The <b>Memcached::getResultCode</b> will return
     *                     <b>Memcached::RES_NOTFOUND</b> if the key does not exist.
     */
    public function delete($key, $time = 0)
    {
        return $this->deleteByKey(null, $key, $time);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Delete an item from a specific server
     *
     * @link http://php.net/manual/en/memcached.deletebykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key        <p>The key to be deleted.</p>
     * @param int    $time       [optional] <p>The amount of time the server will wait to delete the item.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_NOTFOUND</b> if the key does not exist.
     */
    public function deleteByKey($server_key, $key, $time = 0)
    {
        if ($time !== 0) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new BadMethodCallException(sprintf('%s does not emulate $time param.', __METHOD__));
        }

        $key_string = $this->_getKey($key);

        if ($this->_writeSocket($server_key, "delete $key_string", true) === 'DELETED') {
            $this->_result_code = self::RES_SUCCESS;
            $this->_result_message = '';

            return true;
        }

        $this->_result_code = self::RES_NOTFOUND;
        $this->_result_message = 'NOT FOUND';

        return false;
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Delete multiple items
     *
     * @link http://php.net/manual/en/memcached.deletemulti.php
     * @param array $keys <p>The keys to be deleted.</p>
     * @param int   $time [optional] <p>The amount of time the server will wait to delete the items.</p>
     * @return bool|array <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                    The <b>Memcached::getResultCode</b> will return
     *                    <b>Memcached::RES_NOTFOUND</b> if the key does not exist.
     */
    public function deleteMulti(array $keys, $time = 0)
    {
        // Note: we use only single default server.
        return $this->deleteMultiByKey(null, $keys, $time);
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Delete multiple items from a specific server
     *
     * @link http://php.net/manual/en/memcached.deletemultibykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param array  $keys       <p>The keys to be deleted.</p>
     * @param int    $time       [optional] <p>The amount of time the server will wait to delete the items.</p>
     * @return bool|array <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_NOTFOUND</b> if the key does not exist.
     */
    public function deleteMultiByKey($server_key, array $keys, $time = 0)
    {
        // Set initial result.
        $this->_result_code = self::RES_SUCCESS;
        $this->_result_message = 'SUCCESS';

        $results = [];

        // todo - bad, loads all values first via get();

        foreach ($keys as $key) {
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($this->getByKey($server_key, $key) || $this->_result_code === self::RES_SUCCESS) {
                $results[$key] = $this->deleteByKey($server_key, $key, $time);
            } else {
                $results[$key] = self::RES_NOTFOUND;

                // Set error result if any failed.
                $this->_result_code = self::RES_NOTFOUND;
                $this->_result_message = 'NOT FOUND';
            }
        }

        // \Memcached::deleteMultiple returns True or False on error - according to http://php.net/manual/en/memcached.deletemulti.php.
        // But it actually returns array of Key=>Result or false according to https://github.com/php-memcached-dev/php-memcached/blob/master/tests/deletemulti.phpt.

        // If we have servers with wrong details added, and missed keys are deleted:
        // 1. result message is 'SERVER HAS FAILED AND IS DISABLED UNTIL TIMED RETRY'
        // 2. missed keys in return array have result 47
        // 3. final result code is 47

        return $results;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Fetch the next result
     *
     * @link http://php.net/manual/en/memcached.fetch.php
     * @return array the next result or <b>FALSE</b> otherwise.
     * The <b>Memcached::getResultCode</b> will return
     * <b>Memcached::RES_END</b> if result set is exhausted.
     */
    public function fetch()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Fetch all the remaining results
     *
     * @link http://php.net/manual/en/memcached.fetchall.php
     * @return array the results or <b>FALSE</b> on failure.
     * Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function fetchAll()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Invalidate all items in the cache
     *
     * @link http://php.net/manual/en/memcached.flush.php
     * @param int $delay [optional] <p>Number of seconds to wait before invalidating the items.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                   Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function flush($delay = 0)
    {
        // todo - execute on all servers.
        if ($this->_writeSocket(null, 'flush_all' . ($delay ? ' ' . (int)$delay : null), true) === 'OK') {
            $this->_result_code = self::RES_SUCCESS;
            $this->_result_message = '';

            return true;
        }

        $this->_result_code = self::RES_SOME_ERRORS;
        $this->_result_message = 'SOME ERRORS WERE REPORTED';

        return false;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Retrieve an item
     *
     * @link http://php.net/manual/en/memcached.get.php
     * @param string   $key       <p>The key of the item to retrieve.</p>
     * @param callable $cache_cb  [optional] <p>Read-through caching callback or <b>NULL</b>.</p>
     * @param float    $cas_token [optional] <p>The variable to store the CAS token in.</p>
     * @return mixed the value stored in the cache or <b>FALSE</b> otherwise.
     *                            The <b>Memcached::getResultCode</b> will return
     *                            <b>Memcached::RES_NOTFOUND</b> if the key does not exist.
     */
    public function get($key, callable $cache_cb = null, &$cas_token = null)
    {
        // Note: we use only single default server.
        return $this->getByKey(null, $key, $cache_cb, $cas_token);
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Gets the keys stored on all the servers
     *
     * @link http://php.net/manual/en/memcached.getallkeys.php
     * @return array the keys stored on all the servers on success or <b>FALSE</b> on failure.
     */
    public function getAllKeys()
    {
        // todo - currently on default server only.
        $server_key = null;

        // Get slabs.
        $slabs = [];

        if ($this->_writeSocket($server_key, 'stats slabs')) {
            while (!$this->_endOfSocket($server_key)) {
                $temp = $this->_readSocket($server_key);

                if ($temp === 'END') {
                    break;
                }

                /** @noinspection NotOptimalRegularExpressionsInspection */
                if (\preg_match('/^STAT\s([0-9]+)\:/', $temp, $slab_temp)) {
                    if (!empty($slab_temp['1'])) {
                        $slabs[$slab_temp['1']] = true;
                    }

                }
            }

            $slabs = \array_keys($slabs);
        }

        // Keys
        $keys = [];

        foreach ($slabs as &$slab) {
            // 0 means no limit of items per slab.
            if ($this->_writeSocket($server_key, "stats cachedump $slab 0", false)) {
                while (!$this->_endOfSocket($server_key)) {
                    $temp = $this->_readSocket($server_key);

                    if ($temp === 'END') {
                        break;
                    }

                    // ITEM key [4 b; 1465467876 s]
                    \preg_match('/^ITEM\s(.*)\s\[[0-9]+\sb\;\s([0-9]+)\ss\]$/', $temp, $key_temp);

                    if (!empty($key_temp['1'])) {
                        $keys[$key_temp['1']] = true;
                    }
                    // note: $key_temp['2'] holds expiration lifetime or set time, not sure.
                }
            }
        }
        unset($slabs, $slab);

        return \array_keys($keys);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Retrieve an item from a specific server
     *
     * @link http://php.net/manual/en/memcached.getbykey.php
     * @param string   $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string   $key        <p>The key of the item to fetch.</p>
     * @param callable $cache_cb   [optional] <p>Read-through caching callback or <b>NULL</b></p>
     * @param float    $cas_token  [optional] <p>The variable to store the CAS token in.</p>
     * @return mixed the value stored in the cache or <b>FALSE</b> otherwise.
     *                             The <b>Memcached::getResultCode</b> will return
     *                             <b>Memcached::RES_NOTFOUND</b> if the key does not exist.
     */
    public function getByKey($server_key, $key, callable $cache_cb = null, &$cas_token = null)
    {
        $key_string = $this->_getKey($key);

        $s = $this->_writeSocket($server_key, "get $key_string", true);

        if (empty($s) || \strpos($s, 'VALUE') !== 0) {
            $this->_result_code = self::RES_FAILURE;
            $this->_result_message = 'Get failed.';

            // Callback, see http://php.net/manual/en/memcached.callbacks.read-through.php
            if ($cache_cb && $cache_cb($this, $key, $s) === true) {
                return $s;
            }

            return false;
        }

        $this->_result_code = self::RES_SUCCESS;
        $this->_result_message = '';

        $value = '';
        $line = '';

        while ($line !== 'END') {
            $value .= $line;
            $line = $this->_readSocket($server_key);
        }

        // todo - how to emulate it?
        $cas_token = 2.0;

        return $this->_unserialize($value);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Request multiple items
     *
     * @link http://php.net/manual/en/memcached.getdelayed.php
     * @param array    $keys     <p>Array of keys to request.</p>
     * @param bool     $with_cas [optional] <p>Whether to request CAS token values also.</p>
     * @param callable $value_cb [optional] <p>The result callback or <b>NULL</b>.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function getDelayed(array $keys, $with_cas = null, callable $value_cb = null)
    {
        // todo
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Request multiple items from a specific server
     *
     * @link http://php.net/manual/en/memcached.getdelayedbykey.php
     * @param string   $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param array    $keys       <p>Array of keys to request.</p>
     * @param bool     $with_cas   [optional] <p>Whether to request CAS token values also.</p>
     * @param callable $value_cb   [optional] <p>The result callback or <b>NULL</b>.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                             Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function getDelayedByKey($server_key, array $keys, $with_cas = null, callable $value_cb = null)
    {
        // todo
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Retrieve multiple items
     *
     * @link http://php.net/manual/en/memcached.getmulti.php
     * @param array $keys       <p>Array of keys to retrieve.</p>
     * @param array $cas_tokens [optional] <p>The variable to store the CAS tokens for the found items.</p>
     * @param int   $flags      [optional] <p>The flags for the get operation.</p>
     * @return mixed the array of found items or <b>FALSE</b> on failure.
     *                          Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function getMulti(array $keys, array &$cas_tokens = null, $flags = null)
    {
        // Note: we use only single default server.
        return $this->getMultiByKey(null, $keys, $cas_tokens, $flags);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Retrieve multiple items from a specific server
     *
     * @link http://php.net/manual/en/memcached.getmultibykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param array  $keys       <p>Array of keys to retrieve.</p>
     * @param string $cas_tokens [optional] <p>The variable to store the CAS tokens for the found items.</p>
     * @param int    $flags      [optional] <p>The flags for the get operation.</p>
     * @return array|false the array of found items or <b>FALSE</b> on failure.
     *                           Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function getMultiByKey($server_key, array $keys, &$cas_tokens = null, $flags = null)
    {
        $key_strings = \array_map([&$this, '_getKey'], $keys);

        $line = $this->_writeSocket($server_key, 'get ' . \implode(' ', $key_strings), true);

        $values = [];

        // Preserve order?
        if ($flags === self::GET_PRESERVE_ORDER) {
            foreach ($keys as $key) {
                $values[$key] = null;
            }
        }

        // No keys.
        if ($line === 'END') {
            $this->_result_code = self::RES_SUCCESS;
            $this->_result_message = '';

            return [];
        }

        // Error.
        if ($line === null || \strpos($line, 'VALUE') !== 0) {
            $this->_result_code = self::RES_FAILURE;
            $this->_result_message = 'Get failed.';

            return false;
        }

        $this->_result_code = self::RES_SUCCESS;
        $this->_result_message = '';

        $loaded_keys = [];

        $current_key = null;

        while ($line) {
            // New key start.
            if ($current_key === null && \strpos($line, 'VALUE') === 0) {
                list(, $current_key, ,) = \explode(' ', $line);

                $current_key = \substr($current_key, \strlen($this->options[self::OPT_PREFIX_KEY]));
            } // End of all keys
            elseif ($line === 'END' && $current_key === null) {
                break;
            } // Data
            else {
                // todo - how to emulate cas token?
                $cas_tokens[$current_key] = '2';

                // Remember loaded key.
                $loaded_keys[$current_key] = true;

                $values[$current_key] = $this->_unserialize($line);
                $current_key = null;
            }

            $line = $this->_readSocket($server_key);
        }

        // Removed non-loaded keys.
        if ($flags === self::GET_PRESERVE_ORDER) {
            foreach ($keys as $key) {
                if (!\array_key_exists($key, $loaded_keys)) {
                    unset($values[$key]);
                }
            }
        }

        return $values;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Retrieve a Memcached option value
     *
     * @link http://php.net/manual/en/memcached.getoption.php
     * @param int $option <p>One of the Memcached::OPT_* constants.</p>
     * @return mixed the value of the requested option, or <b>FALSE</b> on
     *                    error.
     */
    public function getOption($option)
    {
        // Always same.
        $this->_result_code = self::RES_SUCCESS;
        $this->_result_message = '';

        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return false;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Return the result code of the last operation
     *
     * @link http://php.net/manual/en/memcached.getresultcode.php
     * @return int Result code of the last Memcached operation.
     */
    public function getResultCode()
    {
        return $this->_result_code;
    }

    /**
     * (PECL memcached &gt;= 1.0.0)<br/>
     * Return the message describing the result of the last operation
     *
     * @link http://php.net/manual/en/memcached.getresultmessage.php
     * @return string Message describing the result of the last Memcached operation.
     */
    public function getResultMessage()
    {
        return $this->_result_message;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Map a key to a server
     *
     * @link http://php.net/manual/en/memcached.getserverbykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @return array|false an array containing three keys of host,
     *                           port, and weight on success or <b>FALSE</b> on failure.
     *                           Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function getServerByKey($server_key)
    {
        return $this->_servers[$server_key] ?? false;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Get the list of the servers in the pool
     *
     * @link http://php.net/manual/en/memcached.getserverlist.php
     * @return array The list of all servers in the server pool.
     */
    public function getServerList()
    {
        return $this->_servers;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Get server pool statistics
     *
     * @link http://php.net/manual/en/memcached.getstats.php
     * @return array Array of server statistics, one entry per server.
     */
    public function getStats()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
    }

    /**
     * (PECL memcached &gt;= 0.1.5)<br/>
     * Get server pool version info
     *
     * @link http://php.net/manual/en/memcached.getversion.php
     * @return array Array of server versions, one entry per server.
     */
    public function getVersion()
    {
        $results = [];

        foreach ($this->_servers as $server_key => $tmp) {
            if (false !== $result = $this->_writeSocket($server_key, 'version', true)) {
                // Strip starting 'VERSION '
                $results[$server_key] = \substr($result, 8);
            } else {
                // fake or invalid hosts are always returned as
                // [fake:11210] => 255.255.255
                $results[$server_key] = '255.255.255';
            }
        }

        return $results;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Increment numeric item's value
     *
     * @link http://php.net/manual/en/memcached.increment.php
     * @param string $key           <p>The key of the item to increment.</p>
     * @param int    $offset        [optional] <p>The amount by which to increment the item's value.</p>
     * @param int    $initial_value [optional] <p>The value to set the item to if it doesn't currently exist.</p>
     * @param int    $expiry        [optional] <p>The expiry time to set on the item.</p>
     * @return int new item's value on success or <b>FALSE</b> on failure.
     */
    public function increment($key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        // Note: we use only single default server.
        return $this->incrementByKey(null, $key, $offset, $initial_value, $expiry);
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Increment numeric item's value, stored on a specific server
     *
     * @link http://php.net/manual/en/memcached.incrementbykey.php
     * @param string $server_key    <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key           <p>The key of the item to increment.</p>
     * @param int    $offset        [optional] <p>The amount by which to increment the item's value.</p>
     * @param int    $initial_value [optional] <p>The value to set the item to if it doesn't currently exist.</p>
     * @param int    $expiry        [optional] <p>The expiry time to set on the item.</p>
     * @return int new item's value on success or <b>FALSE</b> on failure.
     */
    public function incrementByKey($server_key, $key, $offset = 1, $initial_value = 0, $expiry = 0)
    {
        // todo - switch to native socket operation.
        if (false === $value = $this->getByKey($server_key, $key)) {
            $value = $initial_value;
        }

        $value += $offset;

        return $this->setByKey($server_key, $key, $value, $expiry) ?: false;
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Check if a persistent connection to memcache is being used
     *
     * @link http://php.net/manual/en/memcached.ispersistent.php
     * @return bool true if Memcache instance uses a persistent connection, false otherwise.
     */
    public function isPersistent()
    {
        return $this->_persistent_id !== null;
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Check if the instance was recently created
     *
     * @link http://php.net/manual/en/memcached.ispristine.php
     * @return bool the true if instance is recently created, false otherwise.
     */
    public function isPristine()
    {
        return $this->_persistent_id === null;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Prepend data to an existing item
     *
     * @link http://php.net/manual/en/memcached.prepend.php
     * @param string $key   <p>The key of the item to prepend the data to.</p>
     * @param string $value <p>The string to prepend.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                      The <b>Memcached::getResultCode</b> will return
     *                      <b>Memcached::RES_NOTSTORED</b> if the key does not exist.
     */
    public function prepend($key, $value)
    {
        // Note: we use only single default server.
        return $this->prependByKey(null, $key, $value);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Prepend data to an existing item on a specific server
     *
     * @link http://php.net/manual/en/memcached.prependbykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key        <p>The key of the item to prepend the data to.</p>
     * @param string $value      <p>The string to prepend.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_NOTSTORED</b> if the key does not exist.
     */
    public function prependByKey($server_key, $key, $value)
    {
        // Prepend does not take <flags> or <exptime> parameters but you must provide them !
        // Doesn't work with enabled compression.

        $key_string = $this->_getKey($key);

        if ($this->_writeSocket($server_key, "prepend $key_string 0 0 " . \strlen($value))) {
            if ($this->_writeSocket($server_key, $value, true) === 'STORED') {
                $this->_result_code = self::RES_SUCCESS;
                $this->_result_message = '';

                return true;
            }

            $this->_result_code = self::RES_NOTSTORED;
            $this->_result_message = 'NOT STORED';

            return false;
        }

        $this->_result_code = self::RES_FAILURE;
        $this->_result_message = 'Prepend failed.';

        return false;
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Close any open connections
     *
     * @link http://php.net/manual/en/memcached.quit.php
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function quit()
    {
        $this->_closeSockets();

        return true;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Replace the item under an existing key
     *
     * @link http://php.net/manual/en/memcached.replace.php
     * @param string $key        <p>The key under which to store the value.</p>
     * @param mixed  $value      <p>The value to store.</p>
     * @param int    $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_NOTSTORED</b> if the key does not exist.
     */
    public function replace($key, $value, $expiration = 0)
    {
        // Note: we use only single default server.
        return $this->replaceByKey(null, $key, $value, $expiration);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Replace the item under an existing key on a specific server
     *
     * @link http://php.net/manual/en/memcached.replacebykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key        <p>The key under which to store the value.</p>
     * @param mixed  $value      <p>The value to store.</p>
     * @param int    $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_NOTSTORED</b> if the key does not exist.
     */
    public function replaceByKey($server_key, $key, $value, $expiration = 0)
    {
        $key_string = $this->_getKey($key);
        $value_string = $this->_serialize($value);

        if ($this->_writeSocket($server_key, "replace $key_string 0 $expiration " . \strlen($value_string))) {
            if ($this->_writeSocket($server_key, $value_string, true) === 'STORED') {
                $this->_result_code = self::RES_SUCCESS;
                $this->_result_message = '';

                return true;
            }

            $this->_result_code = self::RES_NOTSTORED;
            $this->_result_message = 'Replace failed.';

            return false;
        }

        $this->_result_code = self::RES_FAILURE;
        $this->_result_message = 'Replace failed.';

        return false;
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Clears all servers from the server list
     *
     * @link http://php.net/manual/en/memcached.resetserverlist.php
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function resetServerList()
    {
        // Close all sockets.
        $this->_closeSockets();

        $this->_servers = [];

        return true;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Store an item
     *
     * @link http://php.net/manual/en/memcached.set.php
     * @param string $key        <p>The key under which to store the value.</p>
     * @param mixed  $value      <p>The value to store.</p>
     * @param int    $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function set($key, $value, $expiration = 0)
    {
        // Note: we use only single default server.
        return $this->setByKey(null, $key, $value, $expiration);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Store an item on a specific server
     *
     * @link http://php.net/manual/en/memcached.setbykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key        <p>The key under which to store the value.</p>
     * @param mixed  $value      <p>The value to store.</p>
     * @param int    $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure. Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function setByKey($server_key, $key, $value, $expiration = 0)
    {
        $key = $this->_getKey($key);
        $value = $this->_serialize($value);
        $expiration = (int)$expiration;

        if ($this->_writeSocket($server_key, "set $key 0 $expiration " . \strlen($value))) {
            if ($this->_writeSocket($server_key, $value, true) === 'STORED') {
                $this->_result_code = self::RES_SUCCESS;
                $this->_result_message = '';

                return true;
            }
        }

        $this->_result_code = self::RES_FAILURE;
        $this->_result_message = 'Set failed.';

        return false;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Store multiple items
     *
     * @link http://php.net/manual/en/memcached.setmulti.php
     * @param array $items      <p>An array of key/value pairs to store on the server.</p>
     * @param int   $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                          Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function setMulti(array $items, $expiration = 0)
    {
        foreach ($items as $key => $value) {
            if (!$this->set($key, $value, $expiration)) {
                return false;
            }
        }

        return true;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Store multiple items on a specific server
     *
     * @link http://php.net/manual/en/memcached.setmultibykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param array  $items      <p>An array of key/value pairs to store on the server.</p>
     * @param int    $expiration [optional] <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function setMultiByKey($server_key, array $items, $expiration = null)
    {
        foreach ($items as $key => $value) {
            if (!$this->setByKey($server_key, $key, $value, $expiration)) {
                return false;
            }
        }

        return true;
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Set a Memcached option
     *
     * @link http://php.net/manual/en/memcached.setoption.php
     * @param int   $option
     * @param mixed $value
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;

        return true;
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Set Memcached options
     *
     * @link http://php.net/manual/en/memcached.setoptions.php
     * @param array $options <p>An associative array of options where the key is the option to set and the value is the new value for the option.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->options[$option] = $value;
        }

        return true;
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Set the credentials to use for authentication
     *
     * @link http://php.net/manual/en/memcached.setsaslauthdata.php
     * @param string $username <p>The username to use for authentication.</p>
     * @param string $password <p>The password to use for authentication. </p>
     * @return void
     */
    public function setSaslAuthData($username, $password)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Set a new expiration on an item
     *
     * @link http://php.net/manual/en/memcached.touch.php
     * @param string $key        <p>The key under which to store the value.</p>
     * @param int    $expiration <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function touch($key, $expiration)
    {
        // Note: we use only single default server.
        return $this->touchByKey(null, $key, $expiration);
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Set a new expiration on an item on a specific server
     *
     * @link http://php.net/manual/en/memcached.touchbykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param string $key        <p>The key under which to store the value.</p>
     * @param int    $expiration <p>The expiration time, defaults to 0. See Expiration Times for more info.</p>
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     *                           Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function touchByKey($server_key, $key, $expiration)
    {
        $key = $this->_getKey($key);
        $expiration = (int)$expiration;

        if ($this->_writeSocket($server_key, "touch $key $expiration", true) === 'TOUCHED') {
            $this->_result_code = self::RES_SUCCESS;
            $this->_result_message = '';

            return true;
        }

        $this->_result_code = self::RES_NOTFOUND;
        $this->_result_message = 'NOT FOUND';

        // With invalid servers we get these:
        //$this->_result_code = self::RES_WRITE_FAILURE;
        //$this->_result_message = 'WRITE FAILURE';

        return false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Own helper methods.
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Get item key
     *
     * @param   string $key
     *
     * @return  string
     */
    protected function _getKey($key)
    {
        return \addslashes($this->options[self::OPT_PREFIX_KEY]) . $key;
    }

    /**
     * Returns socket by server key.
     *
     * @param string|null $server_key Default server socket is used if NULL.
     * @return resource|false
     */
    protected function _getSocket($server_key = null)
    {
        // Use default server key.
        if ($server_key === null) {
            if ($this->_current_server_key === null) {
                // Check if we have servers.
                if (empty($this->_servers)) {
                    $this->_result_code = self::RES_NO_SERVERS;
                    $this->_result_message = 'NO SERVERS DEFINED';

                    return false;
                }

                // Use first server by default.
                $this->_current_server_key = \key($this->_servers);
            }

            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $server_key = $this->_current_server_key;
        }

        if (!isset($this->_sockets[$server_key])) {
            // Check that server key exists.
            if (!isset($this->_servers[$server_key])) {
                return false;
            }

            $server = $this->_servers[$server_key];

            if (false === $this->_sockets[$server_key] = \fsockopen($server['host'], $server['port'], $error,
                    $errstr)) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                throw new \RuntimeException(\sprintf('%s failed: connecting to %s error: [%s] %s', __CLASS__,
                    $server_key, $error, $errstr));
            }
        }

        return $this->_sockets[$server_key];
    }

    /**
     * Write data to socket.
     *
     * @param  string  $server_key
     * @param  string  $cmd
     * @param  boolean $return_result
     * @param  int     $result_length
     * @return mixed
     */
    protected function _writeSocket($server_key, $cmd, $return_result = false, $result_length = null)
    {
        if (false !== $socket = $this->_getSocket($server_key)) {
            if (\fwrite($socket, $cmd . "\r\n") !== 0) {
                return $return_result ? $this->_readSocket($server_key, $result_length) : true;
            }
        }

        return false;
    }

    /**
     * Read line from socket.
     *
     * @param  string $server_key
     * @param   int   $length
     * @return  string|false
     */
    protected function _readSocket($server_key, $length = null)
    {
        if (false !== $socket = $this->_getSocket($server_key)) {
            // Strip last two \r\n from line!
            return $length ? \fgets($socket, $length) : \substr(\fgets($socket), 0, -2);
        }

        return false;
    }

    /**
     * Tests for end-of-file on a socket.
     *
     * @param  string $server_key
     * @return bool
     */
    protected function _endOfSocket($server_key = null)
    {
        if (false !== $socket = $this->_getSocket($server_key)) {
            return \feof($socket);
        }

        return false;
    }

    /**
     * Closes socket connections.
     *
     * @return bool
     */
    protected function _closeSockets()
    {
        foreach ($this->_sockets as $socket) {
            \fclose($socket);
        }

        return true;
    }

    /**
     * Serialize a value.
     *
     * @param  mixed $value
     * @return string
     */
    protected function _serialize($value)
    {
        switch ($this->options[self::OPT_SERIALIZER]) {
            case self::SERIALIZER_IGBINARY:
                if (\function_exists('igbinary_serialize')) {
                    return \igbinary_serialize($value);
                }
                break;

            case self::SERIALIZER_JSON:
                return \json_encode($value);

            case self::SERIALIZER_JSON_ARRAY:
                return \json_encode($value, true);
        }

        return \serialize($value);
    }

    /**
     * Unserialize a value.
     *
     * @param  string $value
     * @return mixed
     */
    protected function _unserialize($value)
    {
        switch ($this->options[self::OPT_SERIALIZER]) {
            case self::SERIALIZER_IGBINARY:
                if (\function_exists('igbinary_unserialize')) {
                    return \igbinary_unserialize($value);
                }
                break;

            case self::SERIALIZER_JSON:
                return \json_decode($value);

            case self::SERIALIZER_JSON_ARRAY:
                return \json_decode($value, true);
        }

        /** @noinspection UnserializeExploitsInspection */
        return \unserialize($value);
    }

    /**
     * Destructor to close opened sockets.
     */
    public function __destruct()
    {
        $this->_closeSockets();
    }
}
