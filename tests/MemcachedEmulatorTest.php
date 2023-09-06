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
        static::assertTrue($this->emulator->add('key1', '1'));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals('1', $this->emulator->get('key1'));
    }

    /**
     * @covers ::add
     */
    public function testAddExists()
    {
        static::assertTrue($this->emulator->set('key1', '12'));
        static::assertFalse($this->emulator->add('key1', '2'));
        static::assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::addByKey
     */
    public function testAddByKeyMissed()
    {
        static::assertTrue($this->emulator->addByKey(static::SERVER_KEY, 'key1', '1'));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals('1', $this->emulator->getByKey(static::SERVER_KEY, 'key1'));
    }

    /**
     * @covers ::addByKey
     */
    public function testAddByKeyExists()
    {
        static::assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        static::assertFalse($this->emulator->addByKey(static::SERVER_KEY, 'key1', '2'));
        static::assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::addServer
     */
    public function testAddServer()
    {
        static::assertTrue($this->emulator->addServer('127.0.0.2', 11211));
    }

    /**
     * @covers ::addServers
     */
    public function testAddServers()
    {
        static::assertTrue($this->emulator->addServers(
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
        static::assertFalse($this->emulator->append('key1', '2'));
        static::assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::append
     */
    public function testAppendExists()
    {
        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertTrue($this->emulator->append('key1', '2'));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals('12', $this->emulator->get('key1'));
    }

    /**
     * @covers ::appendByKey
     */
    public function testAppendByKeyMissed()
    {
        static::assertFalse($this->emulator->appendByKey(static::SERVER_KEY, 'key1', '1'));
        static::assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::appendByKey
     */
    public function testAppendByKeyExists()
    {
        static::assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        static::assertTrue($this->emulator->appendByKey(static::SERVER_KEY, 'key1', '1'));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals('11', $this->emulator->getByKey(static::SERVER_KEY, 'key1'));
    }

    /**
     * @covers ::cas
     */
    public function testCas()
    {
        static::assertTrue($this->emulator->set('key1', '1'));

        $result = $this->emulator->get('key1', null, MemcachedEmulator::GET_EXTENDED);
        if (!\array_key_exists('value', $result) || !\array_key_exists('cas', $result)) {
            static::markTestIncomplete('Could not get cas token.');
        }

        static::assertFalse($this->emulator->cas(767867, 'key1', '2'));
        static::assertTrue($this->emulator->cas($result['cas'], 'key1', '2'));
        static::assertEquals('2', $this->emulator->get('key1'));
    }

    /**
     * @covers ::casByKey
     */
    public function testCasByKey()
    {
        static::assertTrue($this->emulator->set('key1', '1'));

        $result = $this->emulator->get('key1', null, MemcachedEmulator::GET_EXTENDED);
        if (!\array_key_exists('value', $result) || !\array_key_exists('cas', $result)) {
            static::markTestIncomplete('Could not get cas token.');
        }

        static::assertFalse($this->emulator->casByKey(767867, static::SERVER_KEY, 'key1', '2'));
        static::assertTrue($this->emulator->casByKey($result['cas'], static::SERVER_KEY, 'key1', '2'));
        static::assertEquals('2', $this->emulator->get('key1'));
    }

    /**
     * @covers ::decrement
     */
    public function testDecrementMissed()
    {
        // If the operation decrease the value below 0, the new value will be 0.
        static::assertEquals(1, $this->emulator->decrement('key1', 1, 2));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals(2, $this->emulator->get('key1'));
    }

    /**
     * @covers ::decrement
     */
    public function testDecrementExists()
    {
        static::assertTrue($this->emulator->set('key1', '3'));

        // If the operation decrease the value below 0, the new value will be 0.
        static::assertEquals(2, $this->emulator->decrement('key1', 1, 2));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals(2, $this->emulator->get('key1'));
    }

    /**
     * @covers ::decrementByKey
     */
    public function testDecrementByKeyMissed()
    {
        // If the operation decrease the value below 0, the new value will be 0.
        static::assertEquals(1, $this->emulator->decrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals(2, $this->emulator->get('key1'));
    }

    /**
     * @covers ::decrement
     */
    public function testDecrementByKeyExists()
    {
        static::assertTrue($this->emulator->set('key1', '3'));

        // If the operation decrease the value below 0, the new value will be 0.
        static::assertEquals(2, $this->emulator->decrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals(2, $this->emulator->get('key1'));
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        static::assertFalse($this->emulator->delete('key1'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertTrue($this->emulator->delete('key1'));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertFalse($this->emulator->get('key1'));
    }

    /**
     * @covers ::deleteByKey
     */
    public function testDeleteByKey()
    {
        static::assertFalse($this->emulator->deleteByKey(static::SERVER_KEY, 'key1'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        static::assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        static::assertTrue($this->emulator->deleteByKey(static::SERVER_KEY, 'key1'));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key1'));
    }

    /**
     * @covers ::deleteMulti
     */
    public function testDeleteMulti()
    {
        static::assertEquals([
            'key1' => MemcachedEmulator::RES_NOTFOUND,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], $this->emulator->deleteMulti(['key1', 'key2']));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertEquals([
            'key1' => true,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], $this->emulator->deleteMulti(['key1', 'key2']));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
        static::assertFalse($this->emulator->get('key1'));

        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertTrue($this->emulator->set('key2', '2'));
        static::assertEquals([
            'key1' => true,
            'key2' => true,
        ], $this->emulator->deleteMulti(['key1', 'key2']));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertFalse($this->emulator->get('key1'));
        static::assertFalse($this->emulator->get('key2'));
    }

    /**
     * @covers ::deleteMultiByKey
     */
    public function testDeleteMultiByKey()
    {
        static::assertEquals([
            'key1' => MemcachedEmulator::RES_NOTFOUND,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], $this->emulator->deleteMultiByKey(static::SERVER_KEY, ['key1', 'key2']));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertEquals([
            'key1' => true,
            'key2' => MemcachedEmulator::RES_NOTFOUND,
        ], $this->emulator->deleteMultiByKey(static::SERVER_KEY, ['key1', 'key2']));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
        static::assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key1'));

        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertTrue($this->emulator->set('key2', '2'));
        static::assertEquals([
            'key1' => true,
            'key2' => true,
        ], $this->emulator->deleteMultiByKey(static::SERVER_KEY, ['key1', 'key2']));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key1'));
        static::assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key2'));
    }

    /**
     * @covers ::flush
     * @see testFlushDelay() as well.
     */
    public function testFlush()
    {
        static::assertTrue($this->emulator->flush());
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertTrue($this->emulator->set('key2', '2'));

        static::assertTrue($this->emulator->flush());
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        static::assertFalse($this->emulator->get('key1'));
        static::assertFalse($this->emulator->get('key2'));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        foreach (static::getTestValues() as $type => $value) {
            static::assertTrue($this->emulator->set('key1', $value), \sprintf('Failed on "%s" value.', $type));
            static::assertEquals($value, $this->emulator->get('key1'));
            /** @noinspection DisconnectedForeachInstructionInspection */
            static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

            // Test case
            $actual = $this->emulator->get('key1', null, MemcachedEmulator::GET_EXTENDED);
            static::assertIsArray($actual);
            static::assertArrayHasKey('value', $actual);
            static::assertArrayHasKey('cas', $actual);
            static::assertEquals($value, $actual['value']);
        }

        static::assertFalse($this->emulator->get('key2'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
    }

    /**
     * @covers ::get
     */
    public function testGetCompressed()
    {
        $this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION, true);
        $this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION_TYPE, MemcachedEmulator::COMPRESSION_ZLIB);

        foreach (static::getTestValues() as $type => $value) {
            static::assertTrue($this->emulator->set('key1', $value), \sprintf('Failed on "%s" value.', $type));
            static::assertEquals($value, $this->emulator->get('key1'));
            /** @noinspection DisconnectedForeachInstructionInspection */
            static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        }

        static::assertFalse($this->emulator->get('key2'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        $this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION, false);
    }

    /**
     * @covers ::getByKey
     */
    public function testGetByKey()
    {
        foreach (static::getTestValues() as $type => $value) {
            static::assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', $value),
                \sprintf('Failed on "%s" value: %s.', $type, $this->emulator->getResultCode()));
            static::assertEquals($value, $this->emulator->getByKey(static::SERVER_KEY, 'key1'));
            /** @noinspection DisconnectedForeachInstructionInspection */
            static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        }

        static::assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key2'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
    }

    /**
     * @covers ::getAllKeys
     */
    public function testGetAllKeys()
    {
        // Delete all existing keys first
        $this->emulator->deleteMulti($this->emulator->getAllKeys());

        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertTrue($this->emulator->set('key2', '2'));

        static::assertEqualsCanonicalizing(['key1', 'key2'], $this->emulator->getAllKeys());
    }

    /**
     * @covers ::getMulti
     */
    public function testGetMulti()
    {
        static::assertEquals([], $this->emulator->getMulti(['key1', 'key2']));

        static::assertTrue($this->emulator->set('key1', '1'));

        static::assertEqualsCanonicalizing(['key1' => '1'], $this->emulator->getMulti(['key1', 'key2']));

        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertTrue($this->emulator->set('key2', '2'));

        static::assertEqualsCanonicalizing(['key1' => '1', 'key2' => '2'], $this->emulator->getMulti(['key1', 'key2']));

        $values = self::getTestValues();
        $keys = \array_keys($values);

        static::assertTrue($this->emulator->setMulti($values));
        static::assertEqualsCanonicalizing($values, $this->emulator->getMulti($keys));
        static::assertEquals(\array_fill_keys($keys, true), $this->emulator->deleteMulti($keys));

        // Test Preserve order: actually always

        // Test CAS
        static::assertTrue($this->emulator->setMulti($values));

        $actual = $this->emulator->getMulti($keys, MemcachedEmulator::GET_EXTENDED);
        static::assertIsArray($actual);

        foreach ($values as $key => $value) {
            static::assertArrayHasKey($key, $actual);
            static::assertArrayHasKey('value', $actual[$key]);
            static::assertArrayHasKey('cas', $actual[$key]);
            static::assertEquals($value, $actual[$key]['value']);
        }
    }

    /**
     * @covers ::getMultiByKey
     */
    public function testGetMultiByKey()
    {
        static::assertEquals([], $this->emulator->getMultiByKey(static::SERVER_KEY, ['key1', 'key2']));

        static::assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));

        static::assertEqualsCanonicalizing(['key1' => '1'],
            $this->emulator->getMultiByKey(static::SERVER_KEY, ['key1', 'key2']));

        static::assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        static::assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key2', '2'));

        static::assertEqualsCanonicalizing(['key1' => '1', 'key2' => '2'],
            $this->emulator->getMultiByKey(static::SERVER_KEY, ['key1', 'key2']));

        $values = self::getTestValues();
        $keys = \array_keys($values);

        static::assertTrue($this->emulator->setMultiByKey(static::SERVER_KEY, $values));
        static::assertEqualsCanonicalizing($values, $this->emulator->getMultiByKey(static::SERVER_KEY, $keys));
        static::assertEquals(\array_fill_keys($keys, true),
            $this->emulator->deleteMultiByKey(static::SERVER_KEY, $keys));
    }

    /**
     * @covers ::getOption
     */
    public function testGetOption()
    {
        static::assertEquals(0, $this->emulator->getOption(1234567));

        // Use always available SERIALIZER_JSON;
        static::assertTrue($this->emulator->setOption(MemcachedEmulator::OPT_SERIALIZER,
            MemcachedEmulator::SERIALIZER_JSON));
        static::assertEquals(MemcachedEmulator::SERIALIZER_JSON,
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
        static::assertTrue($this->emulator->set('key1', '1'));

        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::getResultMessage
     */
    public function testGetResultMessage()
    {
        static::assertEquals('', $this->emulator->getResultMessage());
    }

    /**
     * @covers ::getServerByKey
     */
    public function testGetServerByKey()
    {
        static::assertFalse($this->emulator->getServerByKey('false_server'));

        $server = $this->emulator->getServerByKey(static::SERVER_KEY);

        static::assertIsArray($server);
        static::assertArrayHasKey('host', $server);
        static::assertArrayHasKey('port', $server);
        static::assertArrayHasKey('weight', $server);
    }

    /**
     * @covers ::getServerList
     */
    public function testGetServerList()
    {
        $servers = $this->emulator->getServerList();

        static::assertIsArray($servers);
        static::assertArrayHasKey('0', $servers);

        $server = \current($servers);

        static::assertIsArray($server);
        static::assertArrayHasKey('host', $server);
        static::assertArrayHasKey('port', $server);
        static::assertArrayHasKey('weight', $server);
    }

    /**
     * @covers ::getStats
     */
    public function testGetStats()
    {
        $stats = $this->emulator->getStats();

        static::assertIsArray($stats);
        static::assertArrayHasKey(static::SERVER_KEY, $stats);

        $stat = $stats[static::SERVER_KEY];

        static::assertArrayHasKey('pid', $stat);
    }

    /**
     * @covers ::getVersion
     */

    public function testGetVersion()
    {
        $version = $this->emulator->getVersion();

        static::assertIsArray($version);
        static::assertCount(\count($this->emulator->getServerList()), $version);
        static::assertArrayHasKey(static::SERVER_KEY, $version);
    }

    /**
     * @covers ::increment
     */
    public function testIncrementMissed()
    {
        // If the operation decrease the value below 0, the new value will be 0.
        static::assertEquals(3, $this->emulator->increment('key1', 1, 2));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals(3, $this->emulator->get('key1'));
    }

    /**
     * @covers ::increment
     */
    public function testIncrementExists()
    {
        static::assertTrue($this->emulator->set('key1', '3'));

        // If the operation decrease the value below 0, the new value will be 0.
        static::assertEquals(4, $this->emulator->increment('key1', 1, 2));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals(4, $this->emulator->get('key1'));
    }

    /**
     * @covers ::incrementByKey
     */
    public function testIncrementByKeyMissed()
    {
        // If the operation decrease the value below 0, the new value will be 0.
        static::assertEquals(3, $this->emulator->incrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals(3, $this->emulator->get('key1'));
    }

    /**
     * @covers ::increment
     */
    public function testIncrementByKeyExists()
    {
        static::assertTrue($this->emulator->set('key1', '3'));

        // If the operation decrease the value below 0, the new value will be 0.
        static::assertEquals(4, $this->emulator->incrementByKey(static::SERVER_KEY, 'key1', 1, 2));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals(4, $this->emulator->get('key1'));
    }

    /**
     * @covers ::isPersistent
     */
    public function testIsPersistent()
    {
        static::assertFalse($this->emulator->isPersistent());
    }

    /**
     * @covers ::isPristine
     */
    public function testIsPristine()
    {
        static::assertTrue($this->emulator->isPristine());
    }

    /**
     * @covers ::prepend
     */
    public function testPrependExists()
    {
        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertTrue($this->emulator->prepend('key1', '2'));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
        static::assertEquals('21', $this->emulator->get('key1'));
    }

    /**
     * @covers ::appendByKey
     */
    public function testPrependByKeyMissed()
    {
        static::assertFalse($this->emulator->prependByKey(static::SERVER_KEY, 'key1', '2'));
        static::assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::quit
     */
    public function testQuit()
    {
        static::assertTrue($this->emulator->quit());
    }

    /**
     * @covers ::replace
     */
    public function testReplaceMissed()
    {
        static::assertFalse($this->emulator->replace('key1', '1'));
        static::assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::replace
     */
    public function testReplaceExists()
    {
        static::assertTrue($this->emulator->set('key1', '12'));
        static::assertTrue($this->emulator->replace('key1', '2'));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::replaceByKey
     */
    public function testReplaceByKeyMissed()
    {
        static::assertFalse($this->emulator->replaceByKey(static::SERVER_KEY, 'key1', '1'));
        static::assertEquals(MemcachedEmulator::RES_NOTSTORED, $this->emulator->getResultCode());
    }

    /**
     * @covers ::replaceByKey
     */
    public function testReplaceByKeyExists()
    {
        static::assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', '1'));
        static::assertTrue($this->emulator->replaceByKey(static::SERVER_KEY, 'key1', '2'));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::resetServerList
     */
    public function testResetServerList()
    {
        static::assertTrue($this->emulator->resetServerList());
        static::assertEquals([], $this->emulator->getServerList());

        $this->emulator->addServer('127.0.0.1', 11211);
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        // Already covered in testGet()
        static::assertTrue(true);
    }

    /**
     * @covers ::setByKey
     */
    public function testSetByKey()
    {
        // Already covered in testGetByKey()
        static::assertTrue(true);
    }

    /**
     * @covers ::setMulti
     */
    public function testSetMulti()
    {
        // Already covered in testGetMulti()
        static::assertTrue(true);
    }

    /**
     * @covers ::setMultiByKey
     */
    public function testSetMultiByKey()
    {
        // Already covered in testGetMultiByKey()
        static::assertTrue(true);
    }

    /**
     * @covers ::setOption
     */
    public function testSetOption()
    {
        static::assertTrue($this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION, false));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        static::assertFalse($this->emulator->setOption(MemcachedEmulator::OPT_COMPRESSION_TYPE, 'invalid'));
        static::assertEquals(MemcachedEmulator::RES_INVALID_ARGUMENTS, $this->emulator->getResultCode());

        static::assertFalse($this->emulator->setOption(MemcachedEmulator::OPT_SERIALIZER, 'invalid'));
        static::assertEquals(MemcachedEmulator::RES_INVALID_ARGUMENTS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::setOptions
     */
    public function testSetOptions()
    {
        static::assertTrue($this->emulator->setOptions([
            MemcachedEmulator::OPT_COMPRESSION => false,
        ]));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        static::assertFalse($this->emulator->setOptions([
            MemcachedEmulator::OPT_COMPRESSION      => false,
            MemcachedEmulator::OPT_COMPRESSION_TYPE => 'invalid',
        ]));
        static::assertEquals(MemcachedEmulator::RES_INVALID_ARGUMENTS, $this->emulator->getResultCode());
    }

    /**
     * @covers ::setSaslAuthData
     */
    public function testSetSaslAuthData()
    {
        try {
            $this->emulator->setSaslAuthData('username', 'pass');
        } catch (\Exception $e) {
            static::assertInstanceOf(\BadMethodCallException::class, $e);
        }
    }

    /**
     * @covers ::touch
     */
    public function testTouch()
    {
        // Execute later, see testTouchExpiration.
        static::assertTrue(true);
    }

    /**
     * @covers ::touchByKey
     */
    public function testTouchByKey()
    {
        // Execute later, see testTouchByKeyExpiration.
        static::assertTrue(true);
    }

    /*
     * All time-related tests finally
     */

    /**
     * @covers ::flush
     */
    public function testFlushDelay()
    {
        static::assertTrue($this->emulator->set('key1', '1'));
        static::assertTrue($this->emulator->set('key2', '2'));

        static::assertTrue($this->emulator->flush(2));
        static::assertEquals(MemcachedEmulator::RES_SUCCESS, $this->emulator->getResultCode());

        static::assertEquals('1', $this->emulator->get('key1'));
        static::assertEquals('2', $this->emulator->get('key2'));

        \sleep(2);

        static::assertFalse($this->emulator->get('key1'));
        static::assertFalse($this->emulator->get('key2'));
    }

    /**
     * @covers ::set
     */
    public function testSetExpiration()
    {
        static::assertTrue($this->emulator->set('key1', '1', 1));

        \sleep(2);

        static::assertFalse($this->emulator->get('key1'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
    }

    /**
     * @covers ::set
     */
    public function testSetMultiExpiration()
    {
        static::assertTrue($this->emulator->setMulti(['key1' => '1', 'key2' => '2'], 1));

        \sleep(2);

        static::assertFalse($this->emulator->get('key1'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        static::assertFalse($this->emulator->get('key2'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
    }

    /**
     * @covers ::touch
     */
    public function testTouchExpiration()
    {
        // Test touch availability.
        if (\version_compare($this->getClientVersion(), '1.4.8', '<')) {
            static::markTestSkipped('"touch" command available since memcached 1.4.8 only.');
        }

        static::assertFalse($this->emulator->touch('key1', 1));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        static::assertTrue($this->emulator->set('key1', 1));
        static::assertTrue($this->emulator->touch('key1', 1));

        \sleep(2);

        static::assertFalse($this->emulator->get('key1'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
    }

    /**
     * @covers ::touchByKey
     */
    public function testTouchByKeyExpiration()
    {
        // Test touch availability.
        if (\version_compare($this->getClientVersion(), '1.4.8', '<')) {
            static::markTestSkipped('"touch" command available since memcached 1.4.8 only.');
        }

        static::assertFalse($this->emulator->touchByKey(static::SERVER_KEY, 'key1', 1));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());

        static::assertTrue($this->emulator->setByKey(static::SERVER_KEY, 'key1', 1));
        static::assertTrue($this->emulator->touchByKey(static::SERVER_KEY, 'key1', 1));

        \sleep(2);

        static::assertFalse($this->emulator->getByKey(static::SERVER_KEY, 'key1'));
        static::assertEquals(MemcachedEmulator::RES_NOTFOUND, $this->emulator->getResultCode());
    }

    /**
     * @return array
     */
    protected static function getTestValues(): array
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
    protected function getClientVersion($server_key = null): ?string
    {
        $server_key = $server_key ?? static::SERVER_KEY;

        $versions = $this->emulator->getVersion();

        return $versions[$server_key] ?? null;
    }
}
