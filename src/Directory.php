<?php

/**
 * Copyright (C) 2018 Spencer Mortensen
 *
 * This file is part of Filesystem.
 *
 * Filesystem is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Filesystem is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Filesystem. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Spencer Mortensen <spencer@lens.guide>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2018 Spencer Mortensen
 */

namespace SpencerMortensen\Filesystem;

use ErrorException;
use SpencerMortensen\Exceptions\Exceptions;
use SpencerMortensen\Filesystem\Exceptions\ResultException;
use SpencerMortensen\Filesystem\Paths\Path;

class Directory
{
	/** @var int */
	private static $mode = 0777;

	/** @var Path */
	private $path;

	public function __construct(Path $path)
	{
		$this->path = $path;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function read($asObjects = true)
	{
		$path = (string)$this->path;

		$names = $this->scan($path);

		if ($asObjects === false) {
			return $names;
		}

		$children = [];

		foreach ($names as $name) {
			$children[] = $this->getChild($name);
		}

		return $children;
	}

	private function scan($directoryPath)
	{
		$contents = [];

		try {
			Exceptions::on();

			$directory = opendir($directoryPath);

			for ($childName = readdir($directory); $childName !== false; $childName = readdir($directory)) {
				if (($childName === '.') || ($childName === '..')) {
					continue;
				}

				$contents[] = $childName;
			}

			closedir($directory);
		} finally {
			Exceptions::off();
		}

		return $contents;
	}

	private function getChild($name)
	{
		$childPath = $this->path->add($name);

		if (is_dir((string)$childPath)) {
			return new Directory($childPath);
		}

		return new File($childPath);
	}

	public function write()
	{
		$path = (string)$this->path;

		if (is_dir($path)) {
			return;
		}

		try {
			Exceptions::on();
			$isCreated = mkdir($path, self::$mode, true);
		} finally {
			Exceptions::off();
		}

		if ($isCreated !== true) {
			throw new ResultException('mkdir', [$path, self::$mode, true], $isCreated);
		}
	}

	public function move(Path $newPath)
	{
		$oldPathString = (string)$this->path;
		$newPathString = (string)$newPath;

		if (!file_exists($oldPathString)) {
			// TODO: improve this exception:
			throw new ErrorException("There is no file to move.");
		}

		if (file_exists($newPathString)) {
			// TODO: improve this exception:
			throw new ErrorException("There is already a file at the destination path.");
		}

		try {
			Exceptions::on();
			$isMoved = rename($oldPathString, $newPathString);
		} finally {
			Exceptions::off();
		}

		if ($isMoved !== true) {
			throw new ResultException('rename', [$oldPathString, $newPathString], $isMoved);
		}

		$this->path = $newPath;
	}

	public function delete()
	{
		$path = (string)$this->path;

		if (!file_exists($path)) {
			return;
		}

		$children = $this->read();

		foreach ($children as $child) {
			$child->delete();
		}

		try {
			Exceptions::on();
			$isDeleted = rmdir($path);
		} finally {
			Exceptions::off();
		}

		if ($isDeleted !== true) {
			throw new ResultException('rmdir', [$path], $isDeleted);
		}
	}

	public function getModifiedTime()
	{
		$path = (string)$this->path;

		try {
			Exceptions::on();
			$time = filemtime($path);
		} finally {
			Exceptions::off();
		}

		if (!is_int($time)) {
			throw new ResultException('filemtime', [$path], $time);
		}

		return $time;
	}
}
