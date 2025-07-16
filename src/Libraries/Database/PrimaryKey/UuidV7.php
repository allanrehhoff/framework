<?php

namespace Database\PrimaryKey;

trait UuidV7 {
	/**
	 * @var \Random\Randomizer
	 */
	private \Random\Randomizer $randomizer;

	/**
	 * Generates a version 7 UUID (Universally Unique Identifier).
	 * 
	 * IMPORTANT:
	 * V7 is a time-based sortable UUID that uses the current time and random bytes.
	 * Do not use this UUID for security purposes as it leaks the time of generation.
	 * V7 by design is not meant to be secure, but rather to be unique and sortable.
	 * It is designed for use in databases and other systems where a unique identifier is needed.
	 * 
	 *
	 * @return string A version 7 UUID in the format xxxxxxxx-xxxx-7xxx-yxxx-xxxxxxxxxxxx
	 *                where x is a hexadecimal digit and y is one of 8, 9, A, or B.
	 *                The UUID is generated using a combination of the current time and random bytes.
	 *                The first 48 bits are the Unix Epoch timestamp in milliseconds,
	 *                and the remaining 80 bits are random bytes.
	 */
	public function generatePrimaryKey(): string {
		// 0. Initialize the randomizer
		$this->randomizer = new \Random\Randomizer();

		// 1. Get 48-bit Unix Epoch timestamp in milliseconds.
		// This is the most significant 48 bits of the UUID.
		$timeMs = (int) (microtime(true) * 1000);

		// Pack the 48-bit timestamp into 6 big-endian (network byte order) bytes.
		// 'n*' packs 16-bit unsigned shorts. So we split our 48-bit timestamp
		// into three 16-bit chunks.
		$t1 = ($timeMs >> 32) & 0xFFFF; // Most significant 16 bits
		$t2 = ($timeMs >> 16) & 0xFFFF; // Middle 16 bits
		$t3 = $timeMs & 0xFFFF;     	 // Least significant 16 bits
		$timestampBinary = pack('n*', $t1, $t2, $t3); // Resulting 6 bytes

		// 2. Get 10 cryptographically secure random bytes for the remaining 80 bits
		// These will form the 'rand_a' and 'rand_b' fields, and the version/variant bits.
		$random_bytes = $this->randomizer->getBytes(10);

		// 3. Construct the full 16-byte UUID binary string.
		// The first 6 bytes are the timestamp, the next 10 are random.
		$uuidBinary = $timestampBinary . $random_bytes;

		// 4. Inject UUIDv7 version (0111 / 7) into the 7th byte (index 6).
		// This byte contains the 4 version bits and the first 4 bits of 'rand_a'.
		// We preserve the lower 4 bits of the original byte and set the upper 4 to '7' (0111).
		$uuidBinary[6] = chr((ord($uuidBinary[6]) & 0x0F) | 0x70); // 0x70 is 01110000 binary

		// 5. Inject UUID variant (10xx) into the 9th byte (index 8).
		// This byte contains the 2 variant bits and the first 6 bits of 'rand_b'.
		// We preserve the lower 6 bits of the original byte and set the upper 2 to '10' (binary).
		$uuidBinary[8] = chr((ord($uuidBinary[8]) & 0x3F) | 0x80); // 0x80 is 10000000 binary

		// 6. Convert the 16-byte binary UUID to its 32-character hexadecimal representation.
		// This performs one single, efficient hex encoding operation.
		$hex = bin2hex($uuidBinary);

		// 7. Format the hex string with hyphens for the final UUID string.
		return sprintf(
			'%s-%s-%s-%s-%s',
			substr($hex, 0, 8),    // Timestamp part 1
			substr($hex, 8, 4),    // Timestamp part 2
			substr($hex, 12, 4),   // Version and rand_a
			substr($hex, 16, 4),   // Variant and rand_b part 1
			substr($hex, 20, 12)   // Rand_b part 2
		);
	}
}
