<?php
namespace Burzum\FileStorage\Test\TestCase\Storage;

use Burzum\FileStorage\Storage\StorageTrait;
use Cake\TestSuite\TestCase;

class TestStorageTrait {
	use StorageTrait;

}

class BasePathBuilderTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.Burzum\FileStorage.FileStorage'
	];

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->StorageTrait = new TestStorageTrait();
	}

	/**
	 * testBeforeDelete
	 *
	 * @return void
	 */
	public function teststorageAdapter() {
		$result = $this->StorageTrait->getStorageAdapter('Local');
		$this->assertTrue(is_a($result, '\Gaufrette\Filesystem'));
	}

	/**
	 * testBeforeDelete
	 *
	 * @return void
	 */
	public function testGetStorageManagerInstance() {
		$result = $this->StorageTrait->storageManager();
		$this->assertTrue(is_a($result, '\Burzum\FileStorage\Storage\StorageManager'));
	}

}
