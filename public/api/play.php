<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$token = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if ($token === '' || !hash_equals(csrf_token(), $token)) {
    json_response(['error' => 'Invalid session token'], 419);
}

$payload = json_decode(file_get_contents('php://input') ?: '{}', true);
$trackId = filter_var($payload['track_id'] ?? null, FILTER_VALIDATE_INT);
if (!$trackId || !CatalogRepository::track((int) $trackId)) {
    json_response(['error' => 'Track not found'], 404);
}

CatalogRepository::recordPlay((int) $trackId);
json_response(['recorded' => true]);

