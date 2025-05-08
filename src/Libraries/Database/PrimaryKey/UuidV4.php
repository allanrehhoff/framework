<?php
namespace Database\PrimaryKey;

trait UuidV4
{
	/**
	 * @var \Random\Randomizer
	 */
	private \Random\Randomizer $randomizer;

	/**
	 * Generates a version 4 UUID (Universally Unique Identifier).
	 *
	 * @return string A version 4 UUID in the format xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
	 */
    public function generatePrimaryKey(): string {
		// Initialize the randomizer
		$this->randomizer = new \Random\Randomizer();

		// Retrieve 16 cryptographically secure random bytes
		$data = $this->randomizer->getBytes(16);

		// Set the version to 0100 (4) - This corresponds to the 13th hexadecimal digit, which is the upper nibble of the 7th byte (index 6).
		// A UUID consists of 16 bytes. The 6th byte (0-indexed) contains the version bits.
		// The mask 0x0f ensures the lower 4 bits of the byte are preserved, while 0x40 sets the upper 4 bits to 0100 (4).
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);

		// Set the variant to 10xx (8, 9, A, or B) - This corresponds to the 17th hexadecimal digit, which is the upper nibble of the 9th byte (index 8).
		// The 8th byte (0-indexed) contains the variant bits.
		// The mask 0x3f ensures the lower 6 bits are preserved, while 0x80 sets the upper 2 bits to 10.
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		// Format the bytes into the standard UUID string representation
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}