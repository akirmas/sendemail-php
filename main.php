<?php
function main($input) {
  $headers = [
    'Content-Type' => 'application/json'
  ];
  $headers = compact('headers');

  try {
    if (!isset($input['body']))
      if (!isset($input['bodyUrl']))
        throw new Exception('', 400);
    else {
      $input['body'] = file_get_contents($input['bodyUrl']);
      if ($input['body'] === false)
        throw new Exception('', 403);
    }
    $body = $input['body'];

    if (isset($input['vars']) && is_array($input['vars'])) {
      require_once(__DIR__.'/utils/assoc.php');
      $body = \assoc\formatString($body, $input['vars']);
    }

    require_once(__DIR__.'/sendEmail.php');
    $output = $headers + [
      'status' => 200,
      'data' => sendEmail(
        $input['username'],
        $input['password'],
        $body,
        $input['receivers'],
        $input['subject'],
        $input['name']
      )
    ];
  } catch (Exception $e) {
    return $headers + [
      'status' => $e->getCode(),
      'data' => ['error' => $e->getMessage()]
    ];
  }
  return $output;
}