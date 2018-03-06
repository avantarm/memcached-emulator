<?php

namespace Avantarm\MemcachedEmulator;

/**
 * Memcached emulator class, Memcached 3.0.4 compatible.
 */
class MemcachedEmulator
{
    /**
     * Predefined Constants
     *
     * @see http://php.net/manual/en/memcached.constants.php
     */

    /**
     * Libmemcached behavior options.
     */
    const OPT_HASH      = 2;
    const HASH_DEFAULT  = 0;
    const HASH_MD5      = 1;
    const HASH_CRC      = 2;
    const HASH_FNV1_64  = 3;
    const HASH_FNV1A_64 = 4;
    const HASH_FNV1_32  = 5;
    const HASH_FNV1A_32 = 6;
    const HASH_HSIEH    = 7;
    const HASH_MURMUR   = 8;

    const OPT_DISTRIBUTION            = 9;
    const DISTRIBUTION_MODULA         = 0;
    const DISTRIBUTION_CONSISTENT     = 1;
    const DISTRIBUTION_VIRTUAL_BUCKET = 6;

    const OPT_LIBKETAMA_COMPATIBLE   = 16;
    const OPT_LIBKETAMA_HASH         = 17;
    const OPT_TCP_KEEPALIVE          = 32;
    const OPT_BUFFER_WRITES          = 10;
    const OPT_BINARY_PROTOCOL        = 18;
    const OPT_NO_BLOCK               = 0;
    const OPT_TCP_NODELAY            = 1;
    const OPT_SOCKET_SEND_SIZE       = 4;
    const OPT_SOCKET_RECV_SIZE       = 5;
    const OPT_CONNECT_TIMEOUT        = 14;
    const OPT_RETRY_TIMEOUT          = 15;
    const OPT_DEAD_TIMEOUT           = 36;
    const OPT_SEND_TIMEOUT           = 19;
    const OPT_RECV_TIMEOUT           = 20;
    const OPT_POLL_TIMEOUT           = 8;
    const OPT_SERVER_FAILURE_LIMIT   = 21;
    const OPT_SERVER_TIMEOUT_LIMIT   = 37;
    const OPT_CACHE_LOOKUPS          = 6;
    const OPT_AUTO_EJECT_HOSTS       = 28;
    const OPT_HASH_WITH_PREFIX_KEY   = 25;
    const OPT_NOREPLY                = 26;
    const OPT_SORT_HOSTS             = 12;
    const OPT_VERIFY_KEY             = 13;
    const OPT_USE_UDP                = 27;
    const OPT_NUMBER_OF_REPLICAS     = 29;
    const OPT_RANDOMIZE_REPLICA_READ = 30;
    const OPT_REMOVE_FAILED_SERVERS  = 35;

    const LIBMEMCACHED_VERSION_HEX = 16777240;
    const OPT_SERIALIZER           = -1003;
    const OPT_STORE_RETRY_COUNT    = -1005;

    /**
     * Supported serializers
     */
    const HAVE_IGBINARY = 0;
    const HAVE_JSON     = 0;
    const HAVE_MSGPACK  = 0;

    /**
     * Feature support
     */
    const HAVE_SESSION = 1;
    const HAVE_SASL    = 1;

    /**
     * Class options.
     */
    const OPT_COMPRESSION      = -1001;
    const OPT_COMPRESSION_TYPE = -1004;
    const OPT_PREFIX_KEY       = -1002;

    /**
     * Serializer constants
     */
    const SERIALIZER_PHP        = 1;
    const SERIALIZER_IGBINARY   = 2;
    const SERIALIZER_JSON       = 3;
    const SERIALIZER_JSON_ARRAY = 4;
    const SERIALIZER_MSGPACK    = 5;

    /**
     * Compression types
     */
    const COMPRESSION_ZLIB   = 1;
    const COMPRESSION_FASTLZ = 2;

    /**
     * Flags for get and getMulti operations.
     */
    const GET_PRESERVE_ORDER = 1;
    const GET_EXTENDED       = 2;

    /**
     * Return values
     */
    const GET_ERROR_RETURN_VALUE               = false;
    const RES_PAYLOAD_FAILURE                  = -1001;
    const RES_SUCCESS                          = 0;
    const RES_FAILURE                          = 1;
    const RES_HOST_LOOKUP_FAILURE              = 2;
    const RES_UNKNOWN_READ_FAILURE             = 7;
    const RES_PROTOCOL_ERROR                   = 8;
    const RES_CLIENT_ERROR                     = 9;
    const RES_SERVER_ERROR                     = 10;
    const RES_WRITE_FAILURE                    = 5;
    const RES_DATA_EXISTS                      = 12;
    const RES_NOTSTORED                        = 14;
    const RES_NOTFOUND                         = 16;
    const RES_PARTIAL_READ                     = 18;
    const RES_SOME_ERRORS                      = 19;
    const RES_NO_SERVERS                       = 20;
    const RES_END                              = 21;
    const RES_ERRNO                            = 26;
    const RES_BUFFERED                         = 32;
    const RES_TIMEOUT                          = 31;
    const RES_BAD_KEY_PROVIDED                 = 33;
    const RES_STORED                           = 15;
    const RES_DELETED                          = 22;
    const RES_STAT                             = 24;
    const RES_ITEM                             = 25;
    const RES_NOT_SUPPORTED                    = 28;
    const RES_FETCH_NOTFINISHED                = 30;
    const RES_SERVER_MARKED_DEAD               = 35;
    const RES_UNKNOWN_STAT_KEY                 = 36;
    const RES_INVALID_HOST_PROTOCOL            = 34;
    const RES_MEMORY_ALLOCATION_FAILURE        = 17;
    const RES_CONNECTION_SOCKET_CREATE_FAILURE = 11;
    const RES_E2BIG                            = 37;
    const RES_INVALID_ARGUMENTS                = 38; // Emulated, doesn't exist in real Memcached.
    const RES_KEY_TOO_BIG                      = 39;
    const RES_SERVER_TEMPORARILY_DISABLED      = 47;
    const RES_SERVER_MEMORY_ALLOCATION_FAILURE = 48;
    const RES_AUTH_PROBLEM                     = 40;
    const RES_AUTH_FAILURE                     = 41;
    const RES_AUTH_CONTINUE                    = 42;

    /**
     * Flags for PHP variables types.
     */
    const MEMC_VAL_IS_STRING     = 0;
    const MEMC_VAL_IS_LONG       = 1;
    const MEMC_VAL_IS_DOUBLE     = 2;
    const MEMC_VAL_IS_BOOL       = 3;
    const MEMC_VAL_IS_SERIALIZED = 4;
    const MEMC_VAL_IS_IGBINARY   = 5;
    const MEMC_VAL_IS_JSON       = 6;
    const MEMC_VAL_IS_MSGPACK    = 7;

    const MEMC_VAL_COMPRESSED         = 0x1; // (1 << 0);
    const MEMC_VAL_COMPRESSION_ZLIB   = 0x2; // (1 << 1);
    const MEMC_VAL_COMPRESSION_FASTLZ = 0x4; // (1 << 2);

    const MEMC_MASK_TYPE     = 0xf; // MEMC_CREATE_MASK(0, 4)
    const MEMC_MASK_INTERNAL = 0xfff0; // MEMC_CREATE_MASK(4, 12)
    const MEMC_MASK_USER     = 0xffff0000; // MEMC_CREATE_MASK(16, 16)

    /**
     * Response options.
     */
    const RESPONSE_VALUE      = 'VALUE';
    const RESPONSE_STAT       = 'STAT';
    const RESPONSE_ITEM       = 'ITEM';
    const RESPONSE_END        = 'END';
    const RESPONSE_DELETED    = 'DELETED';
    const RESPONSE_NOT_FOUND  = 'NOT_FOUND';
    const RESPONSE_OK         = 'OK';
    const RESPONSE_EXISTS     = 'EXISTS';
    const RESPONSE_ERROR      = 'ERROR';
    const RESPONSE_RESET      = 'RESET';
    const RESPONSE_STORED     = 'STORED';
    const RESPONSE_NOT_STORED = 'NOT_STORED';
    const RESPONSE_TOUCHED    = 'TOUCHED';
    const RESPONSE_VERSION    = 'VERSION';

    const RESPONSE_CLIENT_ERROR = 'CLIENT_ERROR';
    const RESPONSE_SERVER_ERROR = 'SERVER_ERROR';

    /**
     * Transfer end signals.
     */
    const END_SIGNALS = [
        self::RESPONSE_OK,
        self::RESPONSE_STORED,
        self::RESPONSE_NOT_FOUND,
        self::RESPONSE_END,
        self::RESPONSE_DELETED,
        self::RESPONSE_EXISTS,
        self::RESPONSE_ERROR,
        self::RESPONSE_RESET,
        self::RESPONSE_NOT_STORED,
        self::RESPONSE_VERSION,
    ];

    const STORE_RESPONSES = [
        self::RESPONSE_STORED     => self::RES_SUCCESS,
        self::RESPONSE_NOT_STORED => self::RES_NOTSTORED,
        self::RESPONSE_EXISTS     => self::RES_DATA_EXISTS,
        self::RESPONSE_NOT_FOUND  => self::RES_NOTFOUND,
    ];

    const RETRIEVE_RESPONSES = [
        self::RESPONSE_END => self::RES_NOTFOUND,
    ];

    const DELETE_RESPONSES = [
        self::RESPONSE_DELETED   => self::RES_SUCCESS,
        self::RESPONSE_NOT_FOUND => self::RES_NOTFOUND,
    ];

    const INCR_DECR_RESPONSES = [
        self::RESPONSE_NOT_FOUND,
    ];

    const TOUCH_RESPONSES = [
        self::RESPONSE_TOUCHED   => self::RES_SUCCESS,
        self::RESPONSE_NOT_FOUND => self::RES_NOTFOUND,
    ];

    /**
     * Unique instance ID.
     *
     * @var string
     */
    protected $persistent_id;

    /**
     * Pristine status.
     *
     * @var bool
     * @see isPristine()
     */
    protected $is_pristine;

    /**
     * Last result code.
     *
     * @var int
     */
    protected $result_code = self::RES_SUCCESS;

    /**
     * Last result message.
     *
     * @var string
     */
    protected $result_message = '';

    /**
     * Servers list array.
     *
     * @var array
     */
    protected $servers = [];

    /**
     * The key of currently used server.
     *
     * @var string
     */
    protected $current_server_key;

    /**
     * Socket connection handles per server key.
     *
     * @var resource[]
     */
    protected $sockets = [];

    /**
     * Options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $options_id;

    /** @var array Dummy option array */
    protected static $default_options = [
        self::OPT_COMPRESSION          => false,
        self::OPT_COMPRESSION_TYPE     => 'fastlz',
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
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Create a Memcached instance
     *
     * @link http://php.net/manual/en/memcached.construct.php
     * @param string $persistent_id [optional]
     */
    public function __construct($persistent_id = null)
    {
        // Store persistent_id.
        $this->persistent_id = $persistent_id;

        // Set pristine status: no persistent_id or persistent_id is firstly created.
        $this->is_pristine = $persistent_id === null || !isset($this->options[$persistent_id]);

        // Init options.
        $this->_initOptions();
    }

    /**
     * @return void
     */
    protected function _initOptions()
    {
        $this->options_id = $this->persistent_id ?? \spl_object_hash($this);

        if (!isset($this->options[$this->options_id])) {
            $this->options[$this->options_id] = static::$default_options;

            // Apply default INI options.
            /** @noinspection UnnecessaryCastingInspection */
            if ('' === $serializer = (string)\ini_get('memcached.serializer')) {
                $serializer = \function_exists('igbinary_serialize') ? self::SERIALIZER_IGBINARY : self::SERIALIZER_PHP;
            }
            $this->setOption(self::OPT_SERIALIZER, $serializer);

            /** @noinspection UnnecessaryCastingInspection */
            if ('' === $compression_type = (string)\ini_get('memcached.compression_type')) {
                $compression_type = 'fastlz';
            }
            $this->setOption(self::OPT_COMPRESSION_TYPE, $compression_type);
        }
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
        $real_key = $this->_getKey($key);
        $expiration = (int)$expiration;

        if (!$this->_serialize($value, $flags, $bytes)) {
            return $this->_return(false, self::RES_PAYLOAD_FAILURE);
        }

        // add <key> <flags> <exptime> <bytes> [noreply]\r\n<value>\r\n
        if (false !== $response = $this->_write($server_key, "add $real_key $flags $expiration $bytes\r\n$value")) {
            // Valid response.
            if (isset(self::STORE_RESPONSES[$response])) {
                $this->result_code = self::STORE_RESPONSES[$response];
                $this->result_message = '';

                return $this->result_code === self::RES_SUCCESS;
            }

            $this->_checkInvalidResponse($server_key, 'add', $response);
        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
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

        if (isset($this->servers[$key])) {
            $this->result_code = self::RES_FAILURE;
            $this->result_message = 'Server already exists.';

            return false;
        }

        $this->servers[$key] = [
            'host'   => $host,
            'port'   => $port,
            'weight' => $weight,
        ];

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
            if (\count($server) !== 3) {
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
        if (!\is_scalar($value)) {
            return $this->_return(false, self::RES_PAYLOAD_FAILURE);
        }

        $real_key = $this->_getKey($key);
        $bytes = \strlen($value);

        // append <key> <flags> <exptime> <bytes> [noreply]\r\n<value>\r\n
        // flags and exptime are ignored.
        if (false !== $response = $this->_write($server_key, "append $real_key 0 0 $bytes\r\n$value")) {
            // Valid response.
            if (isset(self::STORE_RESPONSES[$response])) {
                $this->result_code = self::STORE_RESPONSES[$response];
                $this->result_message = '';

                return $this->result_code === self::RES_SUCCESS;
            }

            $this->_checkInvalidResponse($server_key, 'append', $response);
        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
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
        $real_key = $this->_getKey($key);
        $expiration = (int)$expiration;

        if (!$this->_serialize($value, $flags, $bytes)) {
            return $this->_return(false, self::RES_PAYLOAD_FAILURE);
        }

        // cas <key> <flags> <exptime> <bytes> <cas unique> [noreply]\r\n
        if (false !== $response = $this->_write($server_key,
                "cas $real_key $flags $expiration $bytes $cas_token\r\n$value")) {
            // Valid response.
            if (isset(self::STORE_RESPONSES[$response])) {
                $this->result_code = self::STORE_RESPONSES[$response];
                $this->result_message = '';

                return $this->result_code === self::RES_SUCCESS;
            }

            $this->_checkInvalidResponse($server_key, 'cas', $response);
        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
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
        $real_key = $this->_getKey($key);
        $expiry = (int)$expiry;

        if (!\is_scalar($offset)) {
            return $this->_return(false, self::RES_PAYLOAD_FAILURE);
        }

        // decr <key> <value> [noreply]\r\n
        if (false !== $response = $this->_write($server_key, "decr $real_key $offset")) {
            // Not found? Use initial value.
            if ($response === self::RESPONSE_NOT_FOUND) {
                // If the operation would decrease the value below 0, the new value will be 0.
                $value = \max(0, $initial_value - $offset);
                return $this->setByKey($server_key, $key, $initial_value, $expiry) ? $value : false;
            }

            // Another invalid response.
            if (isset(self::STORE_RESPONSES[$response])) {

                // Another response
                $this->result_code = self::STORE_RESPONSES[$response];
                $this->result_message = '';

                return false;
            }

            // Check possible invalid response.
            $this->_checkInvalidResponse($server_key, 'decr', $response);
        }

        return (int)$response;
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
            throw new \BadMethodCallException(\sprintf('%s does not emulate $time param.', __METHOD__));
        }

        $real_key = $this->_getKey($key);

        // delete <key> [<time>] [noreply]\r\n
        if (false !== $response = $this->_write($server_key, "delete $real_key")) {
            // Valid response.
            if (isset(self::DELETE_RESPONSES[$response])) {
                $this->result_code = self::DELETE_RESPONSES[$response];
                $this->result_message = '';

                return $this->result_code === self::RES_SUCCESS;
            }

            $this->_checkInvalidResponse($server_key, 'delete', $response);
        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Delete multiple items
     *
     * @link http://php.net/manual/en/memcached.deletemulti.php
     * @param array $keys <p>The keys to be deleted.</p>
     * @param int   $time [optional] <p>The amount of time the server will wait to delete the items.</p>
     * @return array Returns array indexed by keys and where values are indicating whether operation succeeded or not.
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
     * @return array Returns array indexed by keys and where values are indicating whether operation succeeded or not.
     *                           The <b>Memcached::getResultCode</b> will return
     *                           <b>Memcached::RES_NOTFOUND</b> if the key does not exist.
     */
    public function deleteMultiByKey($server_key, array $keys, $time = 0)
    {
        if ($time !== 0) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new \BadMethodCallException(\sprintf('%s does not emulate $time param.', __METHOD__));
        }

        // Set initial result.
        $this->result_code = self::RES_SUCCESS;
        $this->result_message = 'SUCCESS';

        $results = [];

        foreach ($keys as $key) {
            if (false === $results[$key] = $this->deleteByKey($server_key, $key, $time)) {
                $results[$key] = self::RES_NOTFOUND;

                // Set error result if any failed.
                $this->result_code = self::RES_NOTFOUND;
                $this->result_message = 'NOT FOUND';
            }
        }

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
        throw new \BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Fetch all the remaining results
     *
     * @link http://php.net/manual/en/memcached.fetchall.php
     * @return array the results or <b>FALSE</b> on failure. Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function fetchAll()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new \BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
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
        foreach (\array_keys($this->servers) as $server_key) {
            // <flush_all[ delay]>
            if (false !== $response = $this->_write($server_key, 'flush_all' . ($delay ? ' ' . (int)$delay : null))) {
                if ($response !== self::RESPONSE_OK) {
                    $this->_checkInvalidResponse(null, 'flush_all', $response);
                }
            }
        }

        return $this->_return(true, self::RES_SUCCESS);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Retrieve an item
     *
     * @link http://php.net/manual/en/memcached.get.php
     * @param string   $key        <p>The key of the item to retrieve.</p>
     * @param callable $cache_cb   [optional] <p>Read-through caching callback or <b>NULL</b>.</p>
     * @param int      $flags      [optional] <p>Flags to control the returned result. When value of
     *                             <b>Memcached::GET_EXTENDED</b> is given will return the CAS token.</p>
     * @return mixed the value stored in the cache or <b>FALSE</b> otherwise.
     *                             The <b>Memcached::getResultCode</b> will return
     *                             <b>Memcached::RES_NOTFOUND</b> if the key does not exist.
     */
    public function get($key, callable $cache_cb = null, $flags = null)
    {
        // Note: we use only single default server.
        return $this->getByKey(null, $key, $cache_cb, $flags);
    }

    /**
     * (PECL memcached &gt;= 2.0.0)<br/>
     * Gets the keys stored on all the servers
     *
     * @link http://php.net/manual/en/memcached.getallkeys.php
     * @return array|false The keys stored on all the servers on success or <b>FALSE</b> on failure.
     */
    public function getAllKeys()
    {
        $keys = [];

        foreach (\array_keys($this->servers) as $server_key) {
            // Get server slabs.
            $slabs = [];

            // stats slabs
            if (false !== $response = $this->_write($server_key, 'stats slabs', $socket)) {
                $this->_checkInvalidResponse($server_key, 'stats', $response);

                while ($response !== self::RESPONSE_END) {
                    if (\preg_match('/^STAT\s(\d+)\:/', $response, $matches) && !empty($matches['1'])) {
                        $slabs[$matches['1']] = true;
                    }

                    // Read next line.
                    $response = $this->_read($socket);
                }

                $slabs = \array_keys($slabs);
            }

            foreach ($slabs as &$slab) {
                // 0 means no limit of items per slab.
                if (false !== $response = $this->_write($server_key, "stats cachedump $slab 0", $socket)) {
                    $this->_checkInvalidResponse($server_key, 'stats', $response);

                    while ($response !== self::RESPONSE_END) {
                        if (\strpos($response, self::RESPONSE_ITEM) === 0) {
                            list(, $key) = \explode(' ', $response);

                            // Collect unique keys.
                            $keys[$key] = true;
                        }

                        // Read next line.
                        $response = $this->_read($socket);
                    }
                }
            }
        }

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
     * @param int      $flags      [optional] <p>Flags to control the returned result. When value of
     *                             <b>Memcached::GET_EXTENDED</b> is given will return the CAS token.</p>
     * @return mixed the value stored in the cache or <b>FALSE</b> otherwise.
     *                             The <b>Memcached::getResultCode</b> will return
     *                             <b>Memcached::RES_NOTFOUND</b> if the key does not exist.
     */
    public function getByKey($server_key, $key, callable $cache_cb = null, $flags = null)
    {
        $real_key = $this->_getKey($key);

        $cas = $flags && ($flags & self::GET_EXTENDED);

        if (false !== $response = $this->_write($server_key, ($cas ? 'gets' : 'get') . " $real_key", $socket)) {
            // Not found
            if ($response === self::RESPONSE_END) {

                // Apply $cache_cb
                // see http://php.net/manual/en/memcached.callbacks.read-through.php
                /** @noinspection PhpUndefinedVariableInspection */
                if ($cache_cb && $cache_cb($this, $real_key, $value) === true) {
                    // Store value, note that expiration is always 0.
                    if ($this->setByKey($server_key, $key, $value, 0)) {
                        // Return cas token?
                        if ($cas) {
                            return $this->getByKey($server_key, $key, null, $flags);
                        }

                        // Return stored value.
                        return $this->_return($value, self::RES_SUCCESS);
                    }

                    // Store failed.
                    return false;
                }

                return $this->_return(false, self::RES_NOTFOUND, 'Key not found.');
            }

            // VALUE <key> <flags> <bytes> [<cas unique>]
            if (\strpos($response, self::RESPONSE_VALUE) === 0) {
                // Read key meta data.
                $meta = \explode(' ', $response);

                /*
                 * $meta[1] holds key
                 * $meta[2] holds flags
                 * $meta[3] holds bytes
                 * $meta[4] holds cas token (if requested via 'gets' command)
                 */

                $value = '';

                if ($meta[3]) {
                    while (\strlen($value) <= $meta[3]) {
                        $value .= \fgets($socket);
                    }

                    // Trim last \r\n
                    if (\strlen($value) !== (int)$meta[3]) {
                        $value = \substr($value, 0, $meta[3]);
                    }
                } else {
                    // 0 bytes? Still fetch empty <data block> once.
                    \fgets($socket);
                }

                // Fetch END
                \fgets($socket);

                // Set success result data.
                $this->result_code = self::RES_SUCCESS;
                $this->result_message = '';

                // Return cas token with value.
                if ($cas) {
                    // see http://php.net/manual/en/memcached.get.php#121119
                    return [
                        'value' => $this->_unserialize($value, (int)$meta[2]),
                        'cas'   => isset($meta[4]) ? (float)$meta[4] : null, // As float!
                    ];
                }

                return $this->_unserialize($value, (int)$meta[2]);
            }

            $this->_checkInvalidResponse($server_key, 'get', $response);
        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
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
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new \BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
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
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        throw new \BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Retrieve multiple items
     *
     * @link http://php.net/manual/en/memcached.getmulti.php
     * @param array $keys       <p>Array of keys to retrieve.</p>
     * @param int   $flags      [optional] <p>The flags for the get operation.</p>
     * @return mixed the array of found items or <b>FALSE</b> on failure.
     *                          Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function getMulti(array $keys, $flags = null)
    {
        // Note: we use only single default server.
        return $this->getMultiByKey(null, $keys, $flags);
    }

    /**
     * (PECL memcached &gt;= 0.1.0)<br/>
     * Retrieve multiple items from a specific server
     *
     * @link http://php.net/manual/en/memcached.getmultibykey.php
     * @param string $server_key <p>The key identifying the server to store the value on or retrieve it from.</p>
     * @param array  $keys       <p>Array of keys to retrieve.</p>
     * @param int    $flags      [optional] <p>The flags for the get operation.</p>
     * @return array|false the array of found items or <b>FALSE</b> on failure.
     *                           Use <b>Memcached::getResultCode</b> if necessary.
     */
    public function getMultiByKey($server_key, array $keys, $flags = null)
    {
        $real_keys = $this->_getKeys($keys);

        $cas = ($flags && ($flags & self::GET_EXTENDED));

        if (false !== $response = $this->_write($server_key, ($cas ? 'gets' : 'get') . ' ' . \implode(' ', $real_keys),
                $socket)) {
            // No keys.
            if ($response === self::RESPONSE_END) {
                return $this->_return([], self::RES_SUCCESS);
            }

            $values = [];

            while ($response !== false) {
                // VALUE <key> <flags> <bytes> [<cas unique>]
                if (\strpos($response, self::RESPONSE_VALUE) === 0) {
                    // Read key meta data.
                    $meta = \explode(' ', $response);

                    /*
                     * $meta[1] holds key
                     * $meta[2] holds flags
                     * $meta[3] holds bytes
                     * $meta[4] holds cas token (if requested via 'gets' command)
                     */

                    $value = '';

                    if ($meta[3]) {
                        while (\strlen($value) <= $meta[3]) {
                            $value .= \fgets($socket);
                        }

                        // Trim last \r\n
                        if (\strlen($value) !== (int)$meta[3]) {
                            $value = \substr($value, 0, $meta[3]);
                        }
                    }

                    // Get requested key.
                    $key = \array_search($meta[1], $real_keys, false);

                    // Return cas token with value.
                    if ($cas) {
                        // see http://php.net/manual/en/memcached.get.php#121119
                        $values[$key] = [
                            'value' => $this->_unserialize($value, (int)$meta[2]),
                            'cas'   => isset($meta[4]) ? (float)$meta[4] : null, // As float!
                        ];
                    } // Return value.
                    else {
                        $values[$key] = $this->_unserialize($value, (int)$meta[2]);
                    }
                }

                $this->_checkInvalidResponse($server_key, 'get', $response);

                // Next VALUE line or final END.
                if (self::RESPONSE_END === $response = $this->_read($socket)) {
                    break;
                }
            }

            $this->result_code = self::RES_SUCCESS;
            $this->result_message = '';

            // Preserve order?
            if ($flags && ($flags & self::GET_PRESERVE_ORDER)) {
                $ordered_values = \array_fill_keys($keys, true);

                foreach ($ordered_values as $k => $v) {
                    if (!\array_key_exists($k, $values)) {
                        unset($ordered_values[$k]);
                    } else {
                        $ordered_values[$k] = $values[$k];
                    }
                }

                return $ordered_values;
            }

            return $values;

        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
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
        // Always same, even for missed options.
        $this->result_code = self::RES_SUCCESS;
        $this->result_message = '';

        // Actually any non-existent option is INT 0.
        return $this->options[$this->options_id][$option] ?? 0;
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
        return $this->result_code;
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
        return $this->result_message;
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
        return $this->servers[$server_key] ?? false;
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
        return \array_values($this->servers);
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
        $stats = [];

        foreach (\array_keys($this->servers) as $server_key) {
            // stats
            if (false !== $response = $this->_write($server_key, 'stats', $socket)) {
                $this->_checkInvalidResponse($server_key, 'stats', $response);

                while ($response !== self::RESPONSE_END) {
                    if (\preg_match('/^STAT\s(\w+)\s(.*)/', $response, $matches)) {
                        $stats[$server_key][$matches[1]] = $matches[2];
                    }

                    // Read next line.
                    $response = $this->_read($socket);
                }
            }
        }

        return $stats;
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

        foreach ($this->servers as $server_key => $tmp) {
            if (false !== $response = $this->_write($server_key, 'version')) {
                if (\strpos($response, self::RESPONSE_VERSION) === 0) {
                    // Strip starting 'VERSION '
                    $results[$server_key] = \substr($response, 8);

                    continue;
                }
            }
            // fake or invalid hosts are always returned as
            // [fake:11210] => 255.255.255
            $results[$server_key] = '255.255.255';
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
        $real_key = $this->_getKey($key);
        $expiry = (int)$expiry;

        if (!\is_scalar($offset)) {
            return $this->_return(false, self::RES_PAYLOAD_FAILURE);
        }

        // incr <key> <value> [noreply]\r\n
        if (false !== $response = $this->_write($server_key, "incr $real_key $offset")) {
            // Not found? Use initial value.
            if ($response === self::RESPONSE_NOT_FOUND) {
                $value = $initial_value + $offset;
                return $this->setByKey($server_key, $key, $value, $expiry) ? $value : false;
            }

            // Another invalid response.
            if (isset(self::STORE_RESPONSES[$response])) {

                // Another response
                $this->result_code = self::STORE_RESPONSES[$response];
                $this->result_message = '';

                return false;
            }

            // Check possible invalid response.
            $this->_checkInvalidResponse($server_key, 'incr', $response);
        }

        return (int)$response;
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
        return $this->persistent_id !== null;
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
        // means the connection to server of the instance is recently created, that is the instance was created
        // without a persistent_id parameter or the first to use the persistent_id.
        return $this->is_pristine;
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
        $real_key = $this->_getKey($key);

        if (!\is_scalar($value)) {
            return $this->_return(false, self::RES_PAYLOAD_FAILURE);
        }

        $bytes = \strlen($value);

        // prepend <key> <flags> <exptime> <bytes> [noreply]\r\n<value>\r\n
        // flags and exptime are ignored.
        if (false !== $response = $this->_write($server_key, "prepend $real_key 0 0 $bytes\r\n$value")) {
            // Valid response.
            if (isset(self::STORE_RESPONSES[$response])) {
                $this->result_code = self::STORE_RESPONSES[$response];
                $this->result_message = '';

                return $this->result_code === self::RES_SUCCESS;
            }

            $this->_checkInvalidResponse($server_key, 'prepend', $response);
        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
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
        // todo - do we need to send 'quit' command for each?
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
        $real_key = $this->_getKey($key);
        $expiration = (int)$expiration;

        if (!$this->_serialize($value, $flags, $bytes)) {
            return $this->_return(false, self::RES_PAYLOAD_FAILURE);
        }

        // replace <key> <flags> <exptime> <bytes> [noreply]\r\n<value>\r\n
        if (false !== $response = $this->_write($server_key, "replace $real_key $flags $expiration $bytes\r\n$value")) {
            // Valid response.
            if (isset(self::STORE_RESPONSES[$response])) {
                $this->result_code = self::STORE_RESPONSES[$response];
                $this->result_message = '';

                return $this->result_code === self::RES_SUCCESS;
            }

            $this->_checkInvalidResponse($server_key, 'replace', $response);
        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
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

        $this->servers = [];

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
        $real_key = $this->_getKey($key);
        $expiration = (int)$expiration;

        if (!$this->_serialize($value, $flags, $bytes)) {
            return $this->_return(false, self::RES_PAYLOAD_FAILURE);
        }

        // set <key> <flags> <exptime> <bytes> [noreply]\r\n<value>\r\n
        if (false !== $response = $this->_write($server_key, "set $real_key $flags $expiration $bytes\r\n$value")) {
            // Valid response.
            if (isset(self::STORE_RESPONSES[$response])) {
                $this->result_code = self::STORE_RESPONSES[$response];
                $this->result_message = '';

                return $this->result_code === self::RES_SUCCESS;
            }

            $this->_checkInvalidResponse($server_key, 'set', $response);
        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
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
        switch ($option) {
            case self::OPT_COMPRESSION;
                $value = (bool)$value;
                break;

            case self::OPT_COMPRESSION_TYPE:
                {
                    switch ($value) {
                        case 'fastlz':
                        case self::COMPRESSION_FASTLZ;
                            if (\function_exists('fastlz_compress')) {
                                $value = self::COMPRESSION_FASTLZ;
                            } // Fallback to ZLIB
                            elseif (\function_exists('gzcompress')) {
                                $value = self::COMPRESSION_ZLIB;
                            } else {
                                return $this->_return(false, self::RES_INVALID_ARGUMENTS);
                            }
                            break;

                        case 'zlib':
                        case self::COMPRESSION_ZLIB;
                            if (\function_exists('gzcompress')) {
                                $value = self::COMPRESSION_ZLIB;
                            } else {
                                return $this->_return(false, self::RES_INVALID_ARGUMENTS);
                            }
                            break;

                        default:
                            return $this->_return(false, self::RES_INVALID_ARGUMENTS);
                    }
                }
                break;

            case self::OPT_SERIALIZER:
                {
                    switch ($value) {
                        case 'igbinary':
                        case self::SERIALIZER_IGBINARY:
                            $value = \function_exists('igbinary_serialize') ? self::SERIALIZER_IGBINARY : self::SERIALIZER_PHP;
                            break;

                        case 'msgpack':
                        case self::SERIALIZER_MSGPACK:
                            $value = \function_exists('msgpack_pack') ? self::SERIALIZER_MSGPACK : self::SERIALIZER_PHP;
                            break;

                        case 'php':
                        case self::SERIALIZER_PHP:
                            $value = self::SERIALIZER_PHP;
                            break;
                        case 'json':
                        case self::SERIALIZER_JSON:
                            $value = self::SERIALIZER_JSON;
                            break;

                        case 'json_array':
                        case self::SERIALIZER_JSON_ARRAY:
                            $value = self::SERIALIZER_JSON_ARRAY;
                            break;

                        default:
                            return $this->_return(false, self::RES_INVALID_ARGUMENTS);
                    }
                }
                break;
        }

        $this->options[$this->options_id][$option] = $value;

        return $this->_return(true, self::RES_SUCCESS);
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
            if (!$this->setOption($option, $value)) {
                return false;
            }
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
        throw new \BadMethodCallException(\sprintf('%s is not emulated.', __METHOD__));
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
        $real_key = $this->_getKey($key);
        $expiration = (int)$expiration;

        // touch <key> <exptime> [noreply]\r\n
        if (false !== $response = $this->_write($server_key, "touch $real_key $expiration")) {
            // Valid response.
            if (isset(self::TOUCH_RESPONSES[$response])) {
                $this->result_code = self::TOUCH_RESPONSES[$response];
                $this->result_message = '';

                return $this->result_code === self::RES_SUCCESS;
            }

            $this->_checkInvalidResponse($server_key, 'touch', $response);
        }

        return $this->_return(false, self::RES_FAILURE, __METHOD__ . ' failed.');
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Own helper methods.
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Returns real item key.
     *
     * @param   string $key
     *
     * @return  string
     */
    protected function _getKey($key)
    {
        return \addslashes($this->options[$this->options_id][self::OPT_PREFIX_KEY] . $key);
    }

    /**
     * Returns real items keys.
     *
     * @param   string[] $keys
     *
     * @return  string[]
     */
    protected function _getKeys(array $keys)
    {
        $real_keys = [];

        foreach ($keys as $key) {
            $real_keys[$key] = \addslashes($this->options[$this->options_id][self::OPT_PREFIX_KEY] . $key);
        }

        return $real_keys;
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
            if ($this->current_server_key === null) {
                // Check if we have servers.
                if (empty($this->servers)) {
                    $this->result_code = self::RES_NO_SERVERS;
                    $this->result_message = 'NO SERVERS DEFINED';

                    return false;
                }

                // Use first server by default.
                $this->current_server_key = \key($this->servers);
            }

            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $server_key = $this->current_server_key;
        }

        if (!isset($this->sockets[$server_key])) {
            // Check that server key exists.
            if (!isset($this->servers[$server_key])) {
                return false;
            }

            $server = $this->servers[$server_key];

            try {
                if (false === $this->sockets[$server_key] = \fsockopen($server['host'], $server['port'], $error,
                        $errstr)) {
                    throw new \RuntimeException(\sprintf('Code %s: %s', $error, $errstr));
                }
            } catch (\Throwable $e) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                throw new \RuntimeException(\sprintf('%s connection failed: connecting to "%s" error: %s', __CLASS__,
                    $server_key, $e->getMessage()));
            }
        }

        return $this->sockets[$server_key];
    }

    /**
     * Writes data to socket and reads first line.
     *
     * @param  string         $server_key
     * @param  string         $cmd
     * @param  resource|false $socket
     * @return string|false False on error.
     */
    protected function _write($server_key, $cmd, &$socket = null)
    {
        if (
            (false !== $socket = $this->_getSocket($server_key))
            &&
            (\fwrite($socket, $cmd . "\r\n") > 0)
            &&
            (false !== $response = \fgets($socket))
        ) {
            // Strip trailing "\r\n"
            return \substr($response, 0, -2);

        }


        return false;
    }

    /**
     * Reads line from socket.
     *
     * @param resource $socket
     * @return string|false
     */
    protected function _read($socket)
    {
        if (false !== $response = \fgets($socket)) {
            // Strip trailing "\r\n"
            return \substr($response, 0, -2);
        }

        return false;
    }

    /**
     * @param string $server_key
     * @param string $command
     * @param string $response
     */
    protected function _checkInvalidResponse($server_key, $command, $response)
    {
        // RESPONSE_ERROR mostly means non-existent command.
        if ($response === self::RESPONSE_ERROR) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new \RuntimeException(
                \sprintf(
                    '%s on sending command "%s" to server "%s".',
                    self::RESPONSE_ERROR,
                    $command,
                    $server_key ?? $this->current_server_key
                )
            );
        }

        if (\preg_match('/' . self::RESPONSE_CLIENT_ERROR . ' (.*)\R?/mu', $response, $error) > 0) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new \RuntimeException(
                \sprintf(
                    '%s error "%s" on sending command "%s" to server "%s".',
                    self::RESPONSE_CLIENT_ERROR,
                    $error[1],
                    $command,
                    $server_key ?? $this->current_server_key
                )
            );
        }

        if (\preg_match('/' . self::RESPONSE_SERVER_ERROR . ' (.*)\R?/mu', $response, $error) > 0) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            throw new \RuntimeException(
                \sprintf(
                    '%s error "%s" on sending command "%s" to server "%s".',
                    self::RESPONSE_SERVER_ERROR,
                    $error[1],
                    $command,
                    $server_key ?? $this->current_server_key
                )
            );
        }
    }

    /**
     * Read line from socket.
     *
     * @param  string $server_key
     * @param   int   $length
     * @return  string|false
     * @deprecated
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
     * @deprecated
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
        foreach ($this->sockets as $i => $socket) {
            \fclose($socket);
            unset($this->sockets[$i]);
        }

        return true;
    }

    /**
     * Serialize a value.
     *
     * @param  mixed $value
     * @param  int   $flags
     * @param  int   $bytes
     * @return bool
     */
    protected function _serialize(&$value, &$flags, &$bytes)
    {
        switch (\gettype($value)) {
            case 'string':
                self::MEMC_VAL_SET_TYPE($flags, self::MEMC_VAL_IS_STRING);
                break;

            case 'integer':
                self::MEMC_VAL_SET_TYPE($flags, self::MEMC_VAL_IS_LONG);
                break;

            case 'double':
                self::MEMC_VAL_SET_TYPE($flags, self::MEMC_VAL_IS_DOUBLE);
                break;

            case 'boolean':
                self::MEMC_VAL_SET_TYPE($flags, self::MEMC_VAL_IS_BOOL);
                break;

            default:
                {

                    switch ($this->options[$this->options_id][self::OPT_SERIALIZER]) {
                        case self::SERIALIZER_IGBINARY:
                            $value = \igbinary_serialize($value);
                            self::MEMC_VAL_SET_TYPE($flags, self::MEMC_VAL_IS_IGBINARY);
                            break;

                        case self::SERIALIZER_JSON:
                        case self::SERIALIZER_JSON_ARRAY:
                            $value = \json_encode($value);
                            self::MEMC_VAL_SET_TYPE($flags, self::MEMC_VAL_IS_JSON);
                            break;

                        case self::SERIALIZER_MSGPACK:
                            /** @noinspection PhpUndefinedFunctionInspection */
                            $value = \msgpack_pack($value);
                            self::MEMC_VAL_SET_TYPE($flags, self::MEMC_VAL_IS_MSGPACK);
                            break;

                        case self::SERIALIZER_PHP:
                        default:
                            $value = \serialize($value);
                            self::MEMC_VAL_SET_TYPE($flags, self::MEMC_VAL_IS_SERIALIZED);
                            break;
                    }
                }
        }

        $value = (string)$value;
        $bytes = \strlen($value);

        // Compress, but not for values below the threshold
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($bytes && $this->options[$this->options_id][self::OPT_COMPRESSION]) {
            // Get compression threshold, 2000 by default.
            /** @noinspection UnnecessaryCastingInspection */
            if ('' === $compression_threshold = (string)\ini_get('memcached.compression_threshold')) {
                $compression_threshold = 2000;
            }

            // Check compression threshold
            if ($bytes > $compression_threshold) {
                // Get compression factor, float 1.3 by default.
                /** @noinspection UnnecessaryCastingInspection */
                if ('' === $compression_factor = (string)\ini_get('memcached.compression_factor')) {
                    $compression_factor = 1.3;
                }

                switch ($this->options[$this->options_id][self::OPT_COMPRESSION_TYPE]) {
                    case 'fastlz':
                    case self::COMPRESSION_FASTLZ:
                        /** @noinspection PhpUndefinedFunctionInspection */
                        $value2 = \fastlz_compress($value);
                        $compression_flag = self::MEMC_VAL_COMPRESSION_FASTLZ;
                        break;

                    case 'zlib':
                    case self::COMPRESSION_ZLIB:
                    default:
                        $value2 = \gzcompress($value);
                        $compression_flag = self::MEMC_VAL_COMPRESSION_ZLIB;
                        break;
                }

                $bytes2 = \strlen($value2);

                /* Compressed length should be not X larger than original. */
                if ($bytes > $bytes2 * $compression_factor) {
                    $value = $value2;
                    $bytes = $bytes2;
                    self::MEMC_VAL_SET_FLAG($flags, self::MEMC_VAL_COMPRESSED | $compression_flag);
                }

                // todo - always raise RES_PAYLOAD_FAILURE on any error.
            }
        }

        // todo - check all serializations and return false on error.
        // todo - always raise RES_PAYLOAD_FAILURE on any error.

        return true;
    }

    /**
     * Unserialize a value.
     *
     * @param  mixed $value
     * @param  int   $flags
     * @return mixed
     */
    protected function _unserialize($value, $flags)
    {
        // Decompress first.
        if (self::MEMC_VAL_HAS_FLAG($flags, self::MEMC_VAL_COMPRESSED)) {
            if (self::MEMC_VAL_HAS_FLAG($flags, self::MEMC_VAL_COMPRESSION_ZLIB)) {
                $value = \gzuncompress($value);
            } elseif (self::MEMC_VAL_HAS_FLAG($flags, self::MEMC_VAL_COMPRESSION_FASTLZ)) {
                /** @noinspection PhpUndefinedFunctionInspection */
                $value = \fastlz_decompress($value);
            }
        }

        switch (self::MEMC_VAL_GET_TYPE($flags)) {
            case self::MEMC_VAL_IS_STRING:
                break;

            case self::MEMC_VAL_IS_LONG:
                $value = (int)$value;
                break;

            case self::MEMC_VAL_IS_DOUBLE:
                $value = (double)$value;
                break;

            case self::MEMC_VAL_IS_BOOL:
                $value = (bool)$value;
                break;

            case self::MEMC_VAL_IS_SERIALIZED:
                /** @noinspection UnserializeExploitsInspection */
                $value = \unserialize($value);
                break;

            case self::MEMC_VAL_IS_IGBINARY:
                $value = \igbinary_unserialize($value);
                break;

            case self::MEMC_VAL_IS_JSON:
                $value = \json_decode($value,
                    $this->options[$this->options_id][self::OPT_SERIALIZER] === self::SERIALIZER_JSON_ARRAY);
                break;

            case self::MEMC_VAL_IS_MSGPACK:
                /** @noinspection PhpUndefinedFunctionInspection */
                $value = \msgpack_unpack($value);
                break;

            default:
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                throw new \RuntimeException('unknown payload type');
        }

        // todo - always raise RES_PAYLOAD_FAILURE on any error.

        return $value;

    }

    /**
     * Utility return.
     *
     * @param mixed  $result
     * @param int    $result_code
     * @param string $result_message
     * @return mixed
     */
    protected function _return($result, $result_code = self::RES_SUCCESS, $result_message = '')
    {
        $this->result_code = $result_code;
        $this->result_message = $result_message;

        return $result;
    }

    /**
     * Destructor to close opened sockets.
     */
    public function __destruct()
    {
        $this->_closeSockets();
    }

    /**
     * @param int $start
     * @param int $n_bits
     * @return int
     */
    public static function MEMC_CREATE_MASK($start, $n_bits)
    {
        return (((1 << $n_bits) - 1) << $start);
    }

    /**
     * @param int $flags
     * @return int
     */
    public static function MEMC_VAL_GET_TYPE($flags)
    {
        return ($flags & self::MEMC_MASK_TYPE);

    }

    /**
     * @param int $flags
     * @param int $type
     */
    public static function MEMC_VAL_SET_TYPE(&$flags, $type)
    {
        $flags |= ($type & self::MEMC_MASK_TYPE);
    }

    /**
     * @param int $flags
     * @return int
     */
    public static function MEMC_VAL_GET_FLAGS($flags)
    {
        return (($flags & self::MEMC_MASK_INTERNAL) >> 4);
    }

    /**
     * @param int $flags
     * @param int $flag
     */
    public static function MEMC_VAL_SET_FLAG(&$flags, $flag)
    {
        $flags |= (($flag << 4) & self::MEMC_MASK_INTERNAL);
    }

    /**
     * @param int $flags
     * @param int $flag
     * @return bool
     */
    public static function MEMC_VAL_HAS_FLAG($flags, $flag)
    {
        return ((self::MEMC_VAL_GET_FLAGS($flags) & $flag) === $flag);
    }

    /**
     * @param int $flags
     * @param int $flag
     */
    public static function MEMC_VAL_DEL_FLAG(&$flags, $flag)
    {
        $flags &= (~(($flag << 4) & self::MEMC_MASK_INTERNAL));
    }
}
