<?php
use Fol\FileSystem;

class FileSystemTest extends PHPUnit_Framework_TestCase {
	public function testFileSystem () {
		$filesystem = new FileSystem();
		$this->assertEquals($filesystem->getPath(), BASE_PATH);

		$filesystem->cd('tests/fol');
		$this->assertEquals($filesystem->getPath(), BASE_PATH.'/tests/fol');

		//Make a new temporary directory
		$filesystem->mkdir('tmp');
		$info = $filesystem->getInfo('tmp');

		$this->assertEquals($info->getPathname(), BASE_PATH.'/tests/fol/tmp');
		$this->assertTrue($info->isDir());
		$this->assertTrue($info->isReadable());
		$this->assertTrue($info->isWritable());

		//Copy a file in the directory
		$filesystem->copy('http://lorempixum.com/50/50', 'tmp/image.jpg');
		$info = $filesystem->getInfo('tmp/image.jpg');

		$this->assertTrue($info->isFile());
		$this->assertTrue($info->isReadable());
		$this->assertTrue($info->isWritable());

		//Explore the directory
		$iterator = $filesystem->getIterator('tmp');
		
		foreach ($iterator as $file) {
			$this->assertEquals($file->getFilename(), 'image.jpg');
		}

		//Remove the directory and its content
		$filesystem->remove('tmp');
		$info = $filesystem->getInfo('tmp/image.jpg');
		$this->assertFalse($info->isFile());
	}
}
