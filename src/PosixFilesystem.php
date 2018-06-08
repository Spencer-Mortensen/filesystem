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

use SpencerMortensen\Filesystem\Exceptions\ResultException;
use SpencerMortensen\Filesystem\Paths\PosixPath;

class PosixFilesystem
{
	public function getPath($input)
	{
		return PosixPath::fromString($input);
	}

	public function getCurrentDirectoryPath()
	{
		$path = getcwd();

		if (!is_string($path)) {
			throw new ResultException('getcwd', array(), $path);
		}

		return PosixPath::fromString($path);
	}
}