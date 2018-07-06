<?php

namespace Avantarm\WincacheEmulator\Tests;

use Avantarm\MemcachedEmulator\MemcachedEmulator;
use PHPUnit\Framework\TestCase;

/**
 * Class WincacheEmulatorTest
 *
 * @package Avantarm\WincacheEmulator
 * @coversDefaultClass \Avantarm\WincacheEmulator\WincacheEmulator
 */
class MemcachedEmulatorTest extends TestCase
{
    const SERVER_KEY = '127.0.0.1:11211';

    /**
     * @var MemcachedEmulator
     */
    protected static $m;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$m = new MemcachedEmulator();

        static::$m->addServer('127.0.0.1', 11211);


        /*
        $m = new \Memcache();
        $m->addServer('127.0.0.1', 11211);

        $s = "a\r\nEND\r\n";

        $m->set('key2', $s);

        var_dump($m->get('key2'));

        var_dump(static::$m->get('key2'));
        */

    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        // Flush doesn't actually delete all keys but only expires.
        //static::$m->flush();

        static::$m->delete('key1');
        static::$m->delete('key2');
    }

    /**
     * @covers ::add
     */
    public function testAddMissed()
    {
        $this->assertTrue(static::$m->add('key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals('1', static::$m->get('key1'));
    }

    /**
     * @covers ::add
     */
    public function testAddExists()
    {
        $this->assertTrue(static::$m->set('key1', '12'));
        $this->assertFalse(static::$m->add('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, static::$m->getResultCode());
    }

    /**
     * @covers ::addByKey
     */
    public function testAddByKeyMissed()
    {
        $this->assertTrue(static::$m->addByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals('1', static::$m->getByKey(static::SERVER_KEY, 'key1'));
    }

    /**
     * @covers ::addByKey
     */
    public function testAddByKeyExists()
    {
        $this->assertTrue(static::$m->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertFalse(static::$m->addByKey(static::SERVER_KEY, 'key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, static::$m->getResultCode());
    }

    /**
     * @covers ::addServer
     */
    public function testAddServer()
    {
        $this->assertTrue(static::$m->addServer('127.0.0.2', 11211));
    }

    /**
     * @covers ::addServers
     */
    public function testAddServers()
    {
        $this->assertTrue(static::$m->addServers(
            [
                ['127.0.0.3', 11211, 100],
                ['127.0.0.4', 11211, 100],
            ]
        ));
    }

    /**
     * @covers ::append
     */
    public function testAppendMissed()
    {
        $this->assertFalse(static::$m->append('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, static::$m->getResultCode());
    }

    /**
     * @covers ::append
     */
    public function testAppendExists()
    {
        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertTrue(static::$m->append('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals('12', static::$m->get('key1'));
    }

    /**
     * @covers ::appendByKey
     */
    public function testAppendByKeyMissed()
    {
        $this->assertFalse(static::$m->appendByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, static::$m->getResultCode());
    }

    /**
     * @covers ::appendByKey
     */
    public function testAppendByKeyExists()
    {
        $this->assertTrue(static::$m->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertTrue(static::$m->appendByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals('11', static::$m->getByKey(static::SERVER_KEY, 'key1'));
    }

    /**
     * @covers ::cas
     */
    public function testCas()
    {
        $this->assertTrue(static::$m->set('key1', '1'));

        $result = static::$m->get('key1', null, MemcachedEmulator::GET_EXTENDED);
        if (!array_key_exists('value', $result) || !array_key_exists('cas', $result)) {
            $this->markTestIncomplete('Could not get cas token.');
        }

        $this->assertFalse(static::$m->cas(767867, 'key1', '2'));
        $this->assertTrue(static::$m->cas($result['cas'], 'key1', '2'));
        $this->assertEquals('2', static::$m->get('key1'));
    }

    /**
     * @covers ::casByKey
     */
    public function testCasByKey()
    {
        $this->assertTrue(static::$m->set('key1', '1'));

        $result = static::$m->get('key1', null, MemcachedEmulator::GET_EXTENDED);
        if (!array_key_exists('value', $result) || !array_key_exists('cas', $result)) {
            $this->markTestIncomplete('Could not get cas token.');
        }

        $this->assertFalse(static::$m->casByKey(767867, static::SERVER_KEY, 'key1', '2'));
        $this->assertTrue(static::$m->casByKey($result['cas'], static::SERVER_KEY, 'key1', '2'));
        $this->assertEquals('2', static::$m->get('key1'));
    }

    /**
     * @covers ::decrement
     */
    public function testDecrementMissed()
    {
        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(1, static::$m->decrement('key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals(2, static::$m->get('key1'));
    }

    /**
     * @covers ::decrement
     */
    public function testDecrementExists()
    {
        $this->assertTrue(static::$m->set('key1', '3'));

        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(2, static::$m->decrement('key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals(2, static::$m->get('key1'));
    }

    /**
     * @covers ::decrementByKey
     */
    public function testDecrementByKeyMissed()
    {
        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(1, static::$m->decrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals(2, static::$m->get('key1'));
    }

    /**
     * @covers ::decrement
     */
    public function testDecrementByKeyExists()
    {
        $this->assertTrue(static::$m->set('key1', '3'));

        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(2, static::$m->decrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals(2, static::$m->get('key1'));
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $this->assertFalse(static::$m->delete('key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());

        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertTrue(static::$m->delete('key1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertFalse(static::$m->get('key1'));
    }

    /**
     * @covers ::deleteByKey
     */
    public function testDeleteByKey()
    {
        $this->assertFalse(static::$m->deleteByKey(static::SERVER_KEY, 'key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());

        $this->assertTrue(static::$m->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertTrue(static::$m->deleteByKey(static::SERVER_KEY, 'key1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertFalse(static::$m->getByKey(static::SERVER_KEY, 'key1'));
    }

    /**
     * @covers ::deleteMulti
     */
    public function testDeleteMulti()
    {
        $this->assertEquals([
            'key1' => MemcachedEmulator::RES_NOTFOUND,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], static::$m->deleteMulti(['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());

        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertEquals([
            'key1' => true,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], static::$m->deleteMulti(['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());
        $this->assertFalse(static::$m->get('key1'));

        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertTrue(static::$m->set('key2', '2'));
        $this->assertEquals([
            'key1' => true,
            'key2' => true,
        ], static::$m->deleteMulti(['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertFalse(static::$m->get('key1'));
        $this->assertFalse(static::$m->get('key2'));
    }

    /**
     * @covers ::deleteMultiByKey
     */
    public function testDeleteMultiByKey()
    {
        $this->assertEquals([
            'key1' => MemcachedEmulator::RES_NOTFOUND,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], static::$m->deleteMultiByKey(static::SERVER_KEY, ['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());

        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertEquals([
            'key1' => true,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], static::$m->deleteMultiByKey(static::SERVER_KEY, ['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());
        $this->assertFalse(static::$m->getByKey(static::SERVER_KEY, 'key1'));

        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertTrue(static::$m->set('key2', '2'));
        $this->assertEquals([
            'key1' => true,
            'key2' => true,
        ], static::$m->deleteMultiByKey(static::SERVER_KEY, ['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertFalse(static::$m->getByKey(static::SERVER_KEY, 'key1'));
        $this->assertFalse(static::$m->getByKey(static::SERVER_KEY, 'key2'));
    }

    /**
     * @covers ::flush
     * @see testFlushDelay() as well.
     */
    public function testFlush()
    {
        $this->assertTrue(static::$m->flush());
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());

        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertTrue(static::$m->set('key2', '2'));

        $this->assertTrue(static::$m->flush());
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());

        $this->assertFalse(static::$m->get('key1'));
        $this->assertFalse(static::$m->get('key2'));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        foreach (static::getTestValues() as $type => $value) {
            $this->assertTrue(static::$m->set('key1', $value), \sprintf('Failed on "%s" value.', $type));
            $this->assertEquals($value, static::$m->get('key1'));
            $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());

            // Test case
            $actual = static::$m->get('key1', null, MemcachedEmulator::GET_EXTENDED);
            $this->assertInternalType('array', $actual);
            $this->assertArrayHasKey('value', $actual);
            $this->assertArrayHasKey('cas', $actual);
            $this->assertEquals($value, $actual['value']);
        }

        $this->assertFalse(static::$m->get('key2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());
    }

    /**
     * @covers ::get
     */
    public function testGetCompressed()
    {
        static::$m->setOption(MemcachedEmulator::OPT_COMPRESSION, true);
        static::$m->setOption(MemcachedEmulator::OPT_COMPRESSION_TYPE, MemcachedEmulator::COMPRESSION_ZLIB);

        foreach (static::getTestValues() as $type => $value) {
            $this->assertTrue(static::$m->set('key1', $value), \sprintf('Failed on "%s" value.', $type));
            $this->assertEquals($value, static::$m->get('key1'));
            $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        }

        $this->assertFalse(static::$m->get('key2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());

        static::$m->setOption(MemcachedEmulator::OPT_COMPRESSION, false);
    }

    /**
     * @covers ::getByKey
     */
    public function testGetByKey()
    {
        foreach (static::getTestValues() as $type => $value) {
            $this->assertTrue(static::$m->setByKey(static::SERVER_KEY, 'key1', $value),
                \sprintf('Failed on "%s" value: %s.', $type, static::$m->getResultCode()));
            $this->assertEquals($value, static::$m->getByKey(static::SERVER_KEY, 'key1'));
            $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        }

        $this->assertFalse(static::$m->getByKey(static::SERVER_KEY, 'key2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());
    }

    /**
     * @covers ::getAllKeys
     */
    public function testGetAllKeys()
    {
        // Delete all existing keys first
        static::$m->deleteMulti(static::$m->getAllKeys());

        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertTrue(static::$m->set('key2', '2'));

        $this->assertEqualsCanonicalize(['key1', 'key2'], static::$m->getAllKeys());
    }

    /**
     * @covers ::getMulti
     */
    public function testGetMulti()
    {
        $this->assertEquals([], static::$m->getMulti(['key1', 'key2']));

        $this->assertTrue(static::$m->set('key1', '1'));

        $this->assertEqualsCanonicalize(['key1' => '1'], static::$m->getMulti(['key1', 'key2']));

        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertTrue(static::$m->set('key2', '2'));

        $this->assertEqualsCanonicalize(['key1' => '1', 'key2' => '2'], static::$m->getMulti(['key1', 'key2']));

        $values = self::getTestValues();
        $keys = array_keys($values);

        $this->assertTrue(static::$m->setMulti($values));
        $this->assertEqualsCanonicalize($values, static::$m->getMulti($keys));
        $this->assertEquals(array_fill_keys($keys, true), static::$m->deleteMulti($keys));

        // Test Preserve order: actually always

        // Test CAS
        $this->assertTrue(static::$m->setMulti($values));

        $actual = static::$m->getMulti($keys, MemcachedEmulator::GET_EXTENDED);
        $this->assertInternalType('array', $actual);

        foreach ($values as $key => $value) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertArrayHasKey('value', $actual[$key]);
            $this->assertArrayHasKey('cas', $actual[$key]);
            $this->assertEquals($value, $actual[$key]['value']);
        }
    }

    /**
     * @covers ::getMultiByKey
     */
    public function testGetMultiByKey()
    {
        $this->assertEquals([], static::$m->getMultiByKey(static::SERVER_KEY, ['key1', 'key2']));

        $this->assertTrue(static::$m->setByKey(static::SERVER_KEY, 'key1', '1'));

        $this->assertEqualsCanonicalize(['key1' => '1'],
            static::$m->getMultiByKey(static::SERVER_KEY, ['key1', 'key2']));

        $this->assertTrue(static::$m->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertTrue(static::$m->setByKey(static::SERVER_KEY, 'key2', '2'));

        $this->assertEqualsCanonicalize(['key1' => '1', 'key2' => '2'],
            static::$m->getMultiByKey(static::SERVER_KEY, ['key1', 'key2']));

        $values = self::getTestValues();
        $keys = array_keys($values);

        $this->assertTrue(static::$m->setMultiByKey(static::SERVER_KEY, $values));
        $this->assertEqualsCanonicalize($values, static::$m->getMultiByKey(static::SERVER_KEY, $keys));
        $this->assertEquals(array_fill_keys($keys, true), static::$m->deleteMultiByKey(static::SERVER_KEY, $keys));
    }

    /**
     * @covers ::getOption
     */
    public function testGetOption()
    {
        $this->assertEquals(0, static::$m->getOption(1234567));

        // Use always available SERIALIZER_JSON;
        $this->assertTrue(static::$m->setOption(MemcachedEmulator::OPT_SERIALIZER, MemcachedEmulator::SERIALIZER_JSON));
        $this->assertEquals(MemcachedEmulator::SERIALIZER_JSON, static::$m->getOption(MemcachedEmulator::OPT_SERIALIZER));

        // Switch back to default serializer.
        static::$m->setOption(MemcachedEmulator::OPT_SERIALIZER, MemcachedEmulator::SERIALIZER_PHP);
    }

    /**
     * @covers ::getResultCode
     */
    public function testGetResultCode()
    {
        // Execute success command to reset result.
        $this->assertTrue(static::$m->set('key1', '1'));

        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
    }

    /**
     * @covers ::getResultMessage
     */
    public function testGetResultMessage()
    {
        $this->assertEquals('', static::$m->getResultMessage());
    }

    /**
     * @covers ::getServerByKey
     */
    public function testGetServerByKey()
    {
        $this->assertFalse(static::$m->getServerByKey('asasas'));

        $server = static::$m->getServerByKey(static::SERVER_KEY);

        $this->assertInternalType('array', $server);
        $this->assertArrayHasKey('host', $server);
        $this->assertArrayHasKey('port', $server);
        $this->assertArrayHasKey('weight', $server);
    }

    /**
     * @covers ::getServerList
     */
    public function testGetServerList()
    {
        $servers = static::$m->getServerList();

        $this->assertInternalType('array', $servers);
        $this->assertArrayHasKey('0', $servers);

        $server = \current($servers);

        $this->assertInternalType('array', $server);
        $this->assertArrayHasKey('host', $server);
        $this->assertArrayHasKey('port', $server);
        $this->assertArrayHasKey('weight', $server);
    }

    /**
     * @covers ::getStats
     */
    public function testGetStats()
    {
        $stats = static::$m->getStats();

        $this->assertInternalType('array', $stats);
        $this->assertArrayHasKey(static::SERVER_KEY, $stats);

        $stat = $stats[static::SERVER_KEY];

        $this->assertArrayHasKey('pid', $stat);
    }

    /**
     * @covers ::getVersion
     */

    public function testGetVersion()
    {
        $version = static::$m->getVersion();

        $this->assertInternalType('array', $version);
        $this->assertCount(\count(static::$m->getServerList()), $version);
        $this->assertArrayHasKey(static::SERVER_KEY, $version);
    }

    /**
     * @covers ::increment
     */
    public function testIncrementMissed()
    {
        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(3, static::$m->increment('key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals(3, static::$m->get('key1'));
    }

    /**
     * @covers ::increment
     */
    public function testIncrementExists()
    {
        $this->assertTrue(static::$m->set('key1', '3'));

        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(4, static::$m->increment('key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals(4, static::$m->get('key1'));
    }

    /**
     * @covers ::incrementByKey
     */
    public function testIncrementByKeyMissed()
    {
        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(3, static::$m->incrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals(3, static::$m->get('key1'));
    }

    /**
     * @covers ::increment
     */
    public function testIncrementByKeyExists()
    {
        $this->assertTrue(static::$m->set('key1', '3'));

        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(4, static::$m->incrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals(4, static::$m->get('key1'));
    }

    /**
     * @covers ::isPersistent
     */
    public function testIsPersistent()
    {
        $this->assertFalse(static::$m->isPersistent());
    }

    /**
     * @covers ::isPristine
     */
    public function testIsPristine()
    {
        $this->assertTrue(static::$m->isPristine());
    }

    /**
     * @covers ::prepend
     */
    public function testPrependExists()
    {
        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertTrue(static::$m->prepend('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
        $this->assertEquals('21', static::$m->get('key1'));
    }

    /**
     * @covers ::appendByKey
     */
    public function testPrependByKeyMissed()
    {
        $this->assertFalse(static::$m->prependByKey(static::SERVER_KEY, 'key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, static::$m->getResultCode());
    }

    /**
     * @covers ::quit
     */
    public function testQuit()
    {
        $this->assertTrue(static::$m->quit());
    }

    /**
     * @covers ::replace
     */
    public function testReplaceMissed()
    {
        $this->assertFalse(static::$m->replace('key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, static::$m->getResultCode());
    }

    /**
     * @covers ::replace
     */
    public function testReplaceExists()
    {
        $this->assertTrue(static::$m->set('key1', '12'));
        $this->assertTrue(static::$m->replace('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
    }

    /**
     * @covers ::replaceByKey
     */
    public function testReplaceByKeyMissed()
    {
        $this->assertFalse(static::$m->replaceByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, static::$m->getResultCode());
    }

    /**
     * @covers ::replaceByKey
     */
    public function testReplaceByKeyExists()
    {
        $this->assertTrue(static::$m->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertTrue(static::$m->replaceByKey(static::SERVER_KEY, 'key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());
    }

    /**
     * @covers ::resetServerList
     */
    public function testResetServerList()
    {
        $this->assertTrue(static::$m->resetServerList());
        $this->assertEquals([], static::$m->getServerList());

        static::$m->addServer('127.0.0.1', 11211);
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        // Already covered in testGet()
        $this->assertTrue(true);
    }

    /**
     * @covers ::setByKey
     */
    public function testSetByKey()
    {
        // Already covered in testGetByKey()
        $this->assertTrue(true);
    }

    /**
     * @covers ::setMulti
     */
    public function testSetMulti()
    {
        // Already covered in testGetMulti()
        $this->assertTrue(true);
    }

    /**
     * @covers ::setMultiByKey
     */
    public function testSetMultiByKey()
    {
        // Already covered in testGetMultiByKey()
        $this->assertTrue(true);
    }

    /**
     * @covers ::setOption
     */
    public function testSetOption()
    {
        $this->assertTrue(static::$m->setOption(MemcachedEmulator::OPT_COMPRESSION, false));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());

        $this->assertFalse(static::$m->setOption(MemcachedEmulator::OPT_COMPRESSION_TYPE, 'invalid'));
        $this->assertEquals(MemcachedEmulator::RES_INVALID_ARGUMENTS, static::$m->getResultCode());

        $this->assertFalse(static::$m->setOption(MemcachedEmulator::OPT_SERIALIZER, 'invalid'));
        $this->assertEquals(MemcachedEmulator::RES_INVALID_ARGUMENTS, static::$m->getResultCode());
    }

    /**
     * @covers ::setOptions
     */
    public function testSetOptions()
    {
        $this->assertTrue(static::$m->setOptions([
            MemcachedEmulator::OPT_COMPRESSION => false,
        ]));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());

        $this->assertFalse(static::$m->setOptions([
            MemcachedEmulator::OPT_COMPRESSION      => false,
            MemcachedEmulator::OPT_COMPRESSION_TYPE => 'invalid',
        ]));
        $this->assertEquals(MemcachedEmulator::RES_INVALID_ARGUMENTS, static::$m->getResultCode());
    }

    /**
     * @covers ::setSaslAuthData
     */
    public function testSetSaslAuthData()
    {
        try {
            static::$m->setSaslAuthData('username', 'pass');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\BadMethodCallException::class, $e);
        }
    }

    /**
     * @covers ::touch
     */
    public function testTouch()
    {
        // Execute later, see testTouchExpiration.
        $this->assertTrue(true);
    }

    /**
     * @covers ::touchByKey
     */
    public function testTouchByKey()
    {
        // Execute later, see testTouchByKeyExpiration.
        $this->assertTrue(true);
    }

    /*
     * All time-related tests finally
     */

    /**
     * @covers ::flush
     */
    public function testFlushDelay()
    {
        $this->assertTrue(static::$m->set('key1', '1'));
        $this->assertTrue(static::$m->set('key2', '2'));

        $this->assertTrue(static::$m->flush(2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, static::$m->getResultCode());

        $this->assertEquals('1', static::$m->get('key1'));
        $this->assertEquals('2', static::$m->get('key2'));

        sleep(2);

        $this->assertFalse(static::$m->get('key1'));
        $this->assertFalse(static::$m->get('key2'));
    }

    /**
     * @covers ::set
     */
    public function testSetExpiration()
    {
        $this->assertTrue(static::$m->set('key1', '1', 1));

        sleep(2);

        $this->assertFalse(static::$m->get('key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());
    }

    /**
     * @covers ::set
     */
    public function testSetMultiExpiration()
    {
        $this->assertTrue(static::$m->setMulti(['key1' => '1', 'key2' => '2'], 1));

        sleep(2);

        $this->assertFalse(static::$m->get('key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());

        $this->assertFalse(static::$m->get('key2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());
    }

    /**
     * @covers ::touch
     */
    public function testTouchExpiration()
    {
        // Test touch availability.
        if (\version_compare($this->getClientVersion(), '1.4.8', '<')) {
            $this->markTestSkipped('"touch" command available since memcached 1.4.8 only.');
        }

        $this->assertFalse(static::$m->touch('key1', 1));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());

        $this->assertTrue(static::$m->set('key1', 1));
        $this->assertTrue(static::$m->touch('key1', 1));

        sleep(2);

        $this->assertFalse(static::$m->get('key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());
    }

    /**
     * @covers ::touchByKey
     */
    public function testTouchByKeyExpiration()
    {
        // Test touch availability.
        if (\version_compare($this->getClientVersion(), '1.4.8', '<')) {
            $this->markTestSkipped('"touch" command available since memcached 1.4.8 only.');
        }

        $this->assertFalse(static::$m->touchByKey(static::SERVER_KEY, 'key1', 1));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());

        $this->assertTrue(static::$m->setByKey(static::SERVER_KEY, 'key1', 1));
        $this->assertTrue(static::$m->touchByKey(static::SERVER_KEY, 'key1', 1));

        sleep(2);

        $this->assertFalse(static::$m->getByKey(static::SERVER_KEY, 'key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, static::$m->getResultCode());
    }

    /**
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    protected function assertEqualsCanonicalize($expected, $actual, $message = '')
    {
        // Use assertEquals(...$canonicalize=true) since keys order is random.
        $this->assertEquals($expected, $actual, $message, 0, 10, true);
    }

    /**
     * @return array
     */
    protected static function getTestValues()
    {
        return [
            'true'        => true,
            'false'       => false,
            '0'           => 0,
            '-1'          => -1,
            'null'        => null,
            'a'           => 'a',
            'END'         => 'END',
            'line_breaks' => "b\nEND\nb",
            'empty_array' => [],
            'array'       => ['a'],
            'object'      => new \stdClass(),
            'text'        => \file_get_contents(__FILE__),
            __METHOD__ => __METHOD__,
        ];
    }

    /**
     * @param string $server_key
     * @return string|null
     */
    protected function getClientVersion($server_key = null)
    {
        $server_key = $server_key ?? static::SERVER_KEY;

        $versions = static::$m->getVersion();

        return $versions[$server_key] ?? null;
    }
}
