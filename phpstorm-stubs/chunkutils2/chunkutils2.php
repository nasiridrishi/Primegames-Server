<?php
/**
 * Generated stub file for code completion purposes
 */

namespace pocketmine\world\format {
final class PalettedBlockArray{

	public function __construct(int $fillEntry){}

	public static function fromData(int $bitsPerBlock, string $wordArray, array $palette) : \pocketmine\world\format\PalettedBlockArray{}

	public function getWordArray() : string{}

	public function getPalette() : array{}

	public function getMaxPaletteSize() : int{}

	public function getBitsPerBlock() : int{}

	public function get(int $x, int $y, int $z) : int{}

	public function set(int $x, int $y, int $z, int $val){}

	public function replace(int $offset, int $val){}

	public function replaceAll(int $oldVal, int $newVal){}

	public function collectGarbage(bool $force = null){}

	public static function getExpectedWordArraySize(int $bitsPerBlock) : int{}
}
}
namespace pocketmine\world\format\io {
final class SubChunkConverter{

	public static function convertSubChunkXZY(string $idArray, string $metaArray) : \pocketmine\world\format\PalettedBlockArray{}

	public static function convertSubChunkYZX(string $idArray, string $metaArray) : \pocketmine\world\format\PalettedBlockArray{}

	public static function convertSubChunkFromLegacyColumn(string $idArray, string $metaArray, int $yOffset) : \pocketmine\world\format\PalettedBlockArray{}

	private function __construct(){}
}
}
