<?php

require_once 'bootstrap.php';

use AJBnet\Core\Traits\MutableObject;
use AJBnet\Core\Traits\ImmutableObject;

echo "=== Demonstrating MutableObject vs ImmutableObject ===\n\n";

// MutableObject example
class MutableExample {
	use MutableObject;
}

echo "MutableObject Example:\n";
$mutable = new MutableExample();
$mutable->name = "Initial Name";
echo "Initial: name = {$mutable->name}\n";

$mutable->name = "Changed Name";
echo "After change: name = {$mutable->name}\n";
echo "✓ Mutable object allows property changes\n\n";

// ImmutableObject example
class ImmutableExample {
	use ImmutableObject;
}

echo "ImmutableObject Example:\n";
$immutable = new ImmutableExample();
$immutable->name = "Initial Name";
echo "Initial: name = {$immutable->name}\n";

try {
	$immutable->name = "Changed Name";
	echo "ERROR: Should not reach this line\n";
} catch (InvalidArgumentException $e) {
	echo "Attempted change failed: {$e->getMessage()}\n";
	echo "Final: name = {$immutable->name}\n";
	echo "✓ Immutable object prevents property changes\n";
}

echo "\n=== Demonstration complete ===\n";