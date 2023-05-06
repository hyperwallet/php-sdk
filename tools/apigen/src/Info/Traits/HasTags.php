<?php declare(strict_types = 1);

namespace ApiGen\Info\Traits;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;


trait HasTags
{
	/** @var string */
	public string $description = '';

	/** @var PhpDocTagValueNode[][] indexed by [tagName][] */
	public array $tags = [];


	public function isDeprecated(): bool
	{
		return isset($this->tags['deprecated']);
	}


	public function isInternal(): bool
	{
		return isset($this->tags['internal']);
	}
}
