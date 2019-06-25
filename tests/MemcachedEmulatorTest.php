<?php
/**
 * This file is part of the Avantarm package.
 * (c) Avantarm <avantarm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Avantarm\MemcachedEmulator\Tests;

use Avantarm\MemcachedEmulator\MemcachedEmulator;
use PHPUnit\Framework\TestCase;

/**
 * Class MemcachedEmulatorTest
 *
 * @package Avantarm\WincacheEmulator
 * @coversDefaultClass \Avantarm\MemcachedEmulator\MemcachedEmulator
 */
class MemcachedEmulatorTest extends TestCase
{
    public const SERVER_KEY = '127.0.0.1:11211';

    /**
     * @var MemcachedEmulator
     */
    protected $emulator;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->emulator = new MemcachedEmulator();

        $this->emulator->addServer('127.0.0.1', 11211);

        // Flush doesn't actually delete all keys but only expires.
        //static::$m->flush();

        $this->emulator->delete('key1');
        $this->emulator->delete('key2');
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        unset($this->emulator);
    }

    /**
     * @covers ::add
     */
    public function testAddMissed()
    {
        $this->assertTrue($this->emulator->add('key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals('1', $this->emulator->get('key1'));
    }

    /**
     * @covers ::add
     */
    public function testAddExists()
    {
        $this->assertTrue($this->emulator->set('key1', '12'));
        $this->assertFalse($this->emulator->add('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::addByKey
     */
    public function testAddByKeyMissed()
    {
        $this->assertTrue($this->emulator->addByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals('1', $this->emulator->getByKey(static::SERVER_KEY, 'key1'));
    }

    /**
     * @covers ::addByKey
     */
    public function testAddByKeyExists()
    {
        $this->assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertFalse($this->emulator->addByKey(static::SERVER_KEY, 'key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::addServer
     */
    public function testAddServer()
    {
        $this->assertTrue($this->emulator->addServer('127.0.0.2', 11211));
    }

    /**
     * @covers ::addServers
     */
    public function testAddServers()
    {
        $this->assertTrue($this->emulator->addServers(
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
        $this->assertFalse($this->emulator->append('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::append
     */
    public function testAppendExists()
    {
        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertTrue($this->emulator->append('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals('12', $this->emulator->get('key1'));
    }

    /**
     * @covers ::appendByKey
     */
    public function testAppendByKeyMissed()
    {
        $this->assertFalse($this->emulator->appendByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::appendByKey
     */
    public function testAppendByKeyExists()
    {
        $this->assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertTrue($this->emulator->appendByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals('11', $this->emulator->getByKey(static::SERVER_KEY, 'key1'));
    }

    /**
     * @covers ::cas
     */
    public function testCas()
    {
        $this->assertTrue($this->emulator->set('key1', '1'));

        $result = $this->emulator->get('key1', null, MemcachedEmulator::GET_EXTENDED);
        if (!\array_key_exists('value', $result) || !\array_key_exists('cas', $result)) {
            $this->markTestIncomplete('Could not get cas token.');
        }

        $this->assertFalse($this->emulator->cas(767867, 'key1', '2'));
        $this->assertTrue($this->emulator->cas($result['cas'], 'key1', '2'));
        $this->assertEquals('2', $this->emulator->get('key1'));
    }

    /**
     * @covers ::casByKey
     */
    public function testCasByKey()
    {
        $this->assertTrue($this->emulator->set('key1', '1'));

        $result = $this->emulator->get('key1', null, MemcachedEmulator::GET_EXTENDED);
        if (!\array_key_exists('value', $result) || !\array_key_exists('cas', $result)) {
            $this->markTestIncomplete('Could not get cas token.');
        }

        $this->assertFalse($this->emulator->casByKey(767867, static::SERVER_KEY, 'key1', '2'));
        $this->assertTrue($this->emulator->casByKey($result['cas'], static::SERVER_KEY, 'key1', '2'));
        $this->assertEquals('2', $this->emulator->get('key1'));
    }

    /**
     * @covers ::decrement
     */
    public function testDecrementMissed()
    {
        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(1, $this->emulator->decrement('key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals(2, $this->emulator->get('key1'));
    }

    /**
     * @covers ::decrement
     */
    public function testDecrementExists()
    {
        $this->assertTrue($this->emulator->set('key1', '3'));

        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(2, $this->emulator->decrement('key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals(2, $this->emulator->get('key1'));
    }

    /**
     * @covers ::decrementByKey
     */
    public function testDecrementByKeyMissed()
    {
        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(1, $this->emulator->decrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals(2, $this->emulator->get('key1'));
    }

    /**
     * @covers ::decrement
     */
    public function testDecrementByKeyExists()
    {
        $this->assertTrue($this->emulator->set('key1', '3'));

        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(2, $this->emulator->decrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals(2, $this->emulator->get('key1'));
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $this->assertFalse($this->emulator->delete('key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertTrue($this->emulator->delete('key1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertFalse($this->emulator->get('key1'));
    }

    /**
     * @covers ::deleteByKey
     */
    public function testDeleteByKey()
    {
        $this->assertFalse($this->emulator->deleteByKey(static::SERVER_KEY, 'key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        $this->assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertTrue($this->emulator->deleteByKey(static::SERVER_KEY, 'key1'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key1'));
    }

    /**
     * @covers ::deleteMulti
     */
    public function testDeleteMulti()
    {
        $this->assertEquals([
            'key1' => MemcachedEmulator::RES_NOTFOUND,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], $this->emulator->deleteMulti(['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertEquals([
            'key1' => true,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], $this->emulator->deleteMulti(['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
        $this->assertFalse($this->emulator->get('key1'));

        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertTrue($this->emulator->set('key2', '2'));
        $this->assertEquals([
            'key1' => true,
            'key2' => true,
        ], $this->emulator->deleteMulti(['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertFalse($this->emulator->get('key1'));
        $this->assertFalse($this->emulator->get('key2'));
    }

    /**
     * @covers ::deleteMultiByKey
     */
    public function testDeleteMultiByKey()
    {
        $this->assertEquals([
            'key1' => MemcachedEmulator::RES_NOTFOUND,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], $this->emulator->deleteMultiByKey(static::SERVER_KEY, ['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertEquals([
            'key1' => true,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], $this->emulator->deleteMultiByKey(static::SERVER_KEY, ['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
        $this->assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key1'));

        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertTrue($this->emulator->set('key2', '2'));
        $this->assertEquals([
            'key1' => true,
            'key2' => true,
        ], $this->emulator->deleteMultiByKey(static::SERVER_KEY, ['key1', 'key2']));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key1'));
        $this->assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key2'));
    }

    /**
     * @covers ::flush
     * @see testFlushDelay() as well.
     */
    public function testFlush()
    {
        $this->assertTrue($this->emulator->flush());
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertTrue($this->emulator->set('key2', '2'));

        $this->assertTrue($this->emulator->flush());
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        $this->assertFalse($this->emulator->get('key1'));
        $this->assertFalse($this->emulator->get('key2'));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        foreach (static::getTestValues() as $type => $value) {
            $this->assertTrue($this->emulator->set('key1', $value), \sprintf('Failed on "%s" value.', $type));
            $this->assertEquals($value, $this->emulator->get('key1'));
            $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

            // Test case
            $actual = $this->emulator->get('key1', null, MemcachedEmulator::GET_EXTENDED);
            $this->assertIsArray($actual);
            $this->assertArrayHasKey('value', $actual);
            $this->assertArrayHasKey('cas', $actual);
            $this->assertEquals($value, $actual['value']);
        }

        $this->assertFalse($this->emulator->get('key2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
    }

    /**
     * @covers ::get
     */
    public function testGetCompressed()
    {
        $this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION, true);
        $this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION_TYPE, MemcachedEmulator::COMPRESSION_ZLIB);

        foreach (static::getTestValues() as $type => $value) {
            $this->assertTrue($this->emulator->set('key1', $value), \sprintf('Failed on "%s" value.', $type));
            $this->assertEquals($value, $this->emulator->get('key1'));
            $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        }

        $this->assertFalse($this->emulator->get('key2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        $this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION, false);
    }

    /**
     * @covers ::getByKey
     */
    public function testGetByKey()
    {
        foreach (static::getTestValues() as $type => $value) {
            $this->assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', $value),
                \sprintf('Failed on "%s" value: %s.', $type, $this->emulator->getResultCode()));
            $this->assertEquals($value, $this->emulator->getByKey(static::SERVER_KEY, 'key1'));
            $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        }

        $this->assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
    }

    /**
     * @covers ::getAllKeys
     */
    public function testGetAllKeys()
    {
        // Delete all existing keys first
        $this->emulator->deleteMulti($this->emulator->getAllKeys());

        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertTrue($this->emulator->set('key2', '2'));

        $this->assertEqualsCanonicalizing(['key1', 'key2'], $this->emulator->getAllKeys());
    }

    /**
     * @covers ::getMulti
     */
    public function testGetMulti()
    {
        $this->assertEquals([], $this->emulator->getMulti(['key1', 'key2']));

        $this->assertTrue($this->emulator->set('key1', '1'));

        $this->assertEqualsCanonicalizing(['key1' => '1'], $this->emulator->getMulti(['key1', 'key2']));

        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertTrue($this->emulator->set('key2', '2'));

        $this->assertEqualsCanonicalizing(['key1' => '1', 'key2' => '2'], $this->emulator->getMulti(['key1', 'key2']));

        $values = self::getTestValues();
        $keys = \array_keys($values);

        $this->assertTrue($this->emulator->setMulti($values));
        $this->assertEqualsCanonicalizing($values, $this->emulator->getMulti($keys));
        $this->assertEquals(\array_fill_keys($keys, true), $this->emulator->deleteMulti($keys));

        // Test Preserve order: actually always

        // Test CAS
        $this->assertTrue($this->emulator->setMulti($values));

        $actual = $this->emulator->getMulti($keys, MemcachedEmulator::GET_EXTENDED);
        $this->assertIsArray($actual);

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
        $this->assertEquals([], $this->emulator->getMultiByKey(static::SERVER_KEY, ['key1', 'key2']));

        $this->assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));

        $this->assertEqualsCanonicalizing(['key1' => '1'],
            $this->emulator->getMultiByKey(static::SERVER_KEY, ['key1', 'key2']));

        $this->assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key2', '2'));

        $this->assertEqualsCanonicalizing(['key1' => '1', 'key2' => '2'],
            $this->emulator->getMultiByKey(static::SERVER_KEY, ['key1', 'key2']));

        $values = self::getTestValues();
        $keys = \array_keys($values);

        $this->assertTrue($this->emulator->setMultiByKey(static::SERVER_KEY, $values));
        $this->assertEqualsCanonicalizing($values, $this->emulator->getMultiByKey(static::SERVER_KEY, $keys));
        $this->assertEquals(\array_fill_keys($keys, true), $this->emulator->deleteMultiByKey(static::SERVER_KEY, $keys));
    }

    /**
     * @covers ::getOption
     */
    public function testGetOption()
    {
        $this->assertEquals(0, $this->emulator->getOption(1234567));

        // Use always available SERIALIZER_JSON;
        $this->assertTrue($this->emulator->setOption(MemcachedEmulator::OPT_SERIALIZER, MemcachedEmulator::SERIALIZER_JSON));
        $this->assertEquals(MemcachedEmulator::SERIALIZER_JSON,
            $this->emulator->getOption(MemcachedEmulator::OPT_SERIALIZER));

        // Switch back to default serializer.
        $this->emulator->setOption(MemcachedEmulator::OPT_SERIALIZER, MemcachedEmulator::SERIALIZER_PHP);
    }

    /**
     * @covers ::getResultCode
     */
    public function testGetResultCode()
    {
        // Execute success command to reset result.
        $this->assertTrue($this->emulator->set('key1', '1'));

        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::getResultMessage
     */
    public function testGetResultMessage()
    {
        $this->assertEquals('', $this->emulator->getResultMessage());
    }

    /**
     * @covers ::getServerByKey
     */
    public function testGetServerByKey()
    {
        $this->assertFalse($this->emulator->getServerByKey('false_server'));

        $server = $this->emulator->getServerByKey(static::SERVER_KEY);

        $this->assertIsArray($server);
        $this->assertArrayHasKey('host', $server);
        $this->assertArrayHasKey('port', $server);
        $this->assertArrayHasKey('weight', $server);
    }

    /**
     * @covers ::getServerList
     */
    public function testGetServerList()
    {
        $servers = $this->emulator->getServerList();

        $this->assertIsArray($servers);
        $this->assertArrayHasKey('0', $servers);

        $server = \current($servers);

        $this->assertIsArray($server);
        $this->assertArrayHasKey('host', $server);
        $this->assertArrayHasKey('port', $server);
        $this->assertArrayHasKey('weight', $server);
    }

    /**
     * @covers ::getStats
     */
    public function testGetStats()
    {
        $stats = $this->emulator->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey(static::SERVER_KEY, $stats);

        $stat = $stats[static::SERVER_KEY];

        $this->assertArrayHasKey('pid', $stat);
    }

    /**
     * @covers ::getVersion
     */

    public function testGetVersion()
    {
        $version = $this->emulator->getVersion();

        $this->assertIsArray($version);
        $this->assertCount(\count($this->emulator->getServerList()), $version);
        $this->assertArrayHasKey(static::SERVER_KEY, $version);
    }

    /**
     * @covers ::increment
     */
    public function testIncrementMissed()
    {
        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(3, $this->emulator->increment('key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals(3, $this->emulator->get('key1'));
    }

    /**
     * @covers ::increment
     */
    public function testIncrementExists()
    {
        $this->assertTrue($this->emulator->set('key1', '3'));

        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(4, $this->emulator->increment('key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals(4, $this->emulator->get('key1'));
    }

    /**
     * @covers ::incrementByKey
     */
    public function testIncrementByKeyMissed()
    {
        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(3, $this->emulator->incrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals(3, $this->emulator->get('key1'));
    }

    /**
     * @covers ::increment
     */
    public function testIncrementByKeyExists()
    {
        $this->assertTrue($this->emulator->set('key1', '3'));

        // If the operation would decrease the value below 0, the new value will be 0.
        $this->assertEquals(4, $this->emulator->incrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals(4, $this->emulator->get('key1'));
    }

    /**
     * @covers ::isPersistent
     */
    public function testIsPersistent()
    {
        $this->assertFalse($this->emulator->isPersistent());
    }

    /**
     * @covers ::isPristine
     */
    public function testIsPristine()
    {
        $this->assertTrue($this->emulator->isPristine());
    }

    /**
     * @covers ::prepend
     */
    public function testPrependExists()
    {
        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertTrue($this->emulator->prepend('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        $this->assertEquals('21', $this->emulator->get('key1'));
    }

    /**
     * @covers ::appendByKey
     */
    public function testPrependByKeyMissed()
    {
        $this->assertFalse($this->emulator->prependByKey(static::SERVER_KEY, 'key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::quit
     */
    public function testQuit()
    {
        $this->assertTrue($this->emulator->quit());
    }

    /**
     * @covers ::replace
     */
    public function testReplaceMissed()
    {
        $this->assertFalse($this->emulator->replace('key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::replace
     */
    public function testReplaceExists()
    {
        $this->assertTrue($this->emulator->set('key1', '12'));
        $this->assertTrue($this->emulator->replace('key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::replaceByKey
     */
    public function testReplaceByKeyMissed()
    {
        $this->assertFalse($this->emulator->replaceByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::replaceByKey
     */
    public function testReplaceByKeyExists()
    {
        $this->assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        $this->assertTrue($this->emulator->replaceByKey(static::SERVER_KEY, 'key1', '2'));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::resetServerList
     */
    public function testResetServerList()
    {
        $this->assertTrue($this->emulator->resetServerList());
        $this->assertEquals([], $this->emulator->getServerList());

        $this->emulator->addServer('127.0.0.1', 11211);
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
        $this->assertTrue($this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION, false));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        $this->assertFalse($this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION_TYPE, 'invalid'));
        $this->assertEquals(MemcachedEmulator::RES_INVALID_ARGUMENTS, $this->emulator->getResultCode());

        $this->assertFalse($this->emulator->setOption(MemcachedEmulator::OPT_SERIALIZER, 'invalid'));
        $this->assertEquals(MemcachedEmulator::RES_INVALID_ARGUMENTS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::setOptions
     */
    public function testSetOptions()
    {
        $this->assertTrue($this->emulator->setOptions([
            MemcachedEmulator::OPT_COMPRESSION => false,
        ]));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        $this->assertFalse($this->emulator->setOptions([
            MemcachedEmulator::OPT_COMPRESSION      => false,
            MemcachedEmulator::OPT_COMPRESSION_TYPE => 'invalid',
        ]));
        $this->assertEquals(MemcachedEmulator::RES_INVALID_ARGUMENTS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::setSaslAuthData
     */
    public function testSetSaslAuthData()
    {
        try {
            $this->emulator->setSaslAuthData('username', 'pass');
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
        $this->assertTrue($this->emulator->set('key1', '1'));
        $this->assertTrue($this->emulator->set('key2', '2'));

        $this->assertTrue($this->emulator->flush(2));
        $this->assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        $this->assertEquals('1', $this->emulator->get('key1'));
        $this->assertEquals('2', $this->emulator->get('key2'));

        \sleep(2);

        $this->assertFalse($this->emulator->get('key1'));
        $this->assertFalse($this->emulator->get('key2'));
    }

    /**
     * @covers ::set
     */
    public function testSetExpiration()
    {
        $this->assertTrue($this->emulator->set('key1', '1', 1));

        \sleep(2);

        $this->assertFalse($this->emulator->get('key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
    }

    /**
     * @covers ::set
     */
    public function testSetMultiExpiration()
    {
        $this->assertTrue($this->emulator->setMulti(['key1' => '1', 'key2' => '2'], 1));

        \sleep(2);

        $this->assertFalse($this->emulator->get('key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        $this->assertFalse($this->emulator->get('key2'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
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

        $this->assertFalse($this->emulator->touch('key1', 1));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        $this->assertTrue($this->emulator->set('key1', 1));
        $this->assertTrue($this->emulator->touch('key1', 1));

        \sleep(2);

        $this->assertFalse($this->emulator->get('key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
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

        $this->assertFalse($this->emulator->touchByKey(static::SERVER_KEY, 'key1', 1));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        $this->assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', 1));
        $this->assertTrue($this->emulator->touchByKey(static::SERVER_KEY, 'key1', 1));

        \sleep(2);

        $this->assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key1'));
        $this->assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
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
            __METHOD__    => __METHOD__,
        ];
    }

    /**
     * @param string $server_key
     * @return string|null
     */
    protected function getClientVersion($server_key = null)
    {
        $server_key = $server_key ?? static::SERVER_KEY;

        $versions = $this->emulator->getVersion();

        return $versions[$server_key] ?? null;
    }
}
