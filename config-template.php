<?php

// TEMPLATE

$config = [
  'TG_CHAT_ID'=> '-1234567891234',
  'TG_BOT_TOKEN'=> '0123456789:AAgfsgS7353R-7t35Sh_zX-235GSogASgjAG',
  'KUSTOMER_API_KEY' => 'YOUR KEY FROM KUSTOMER',
  'INTERNAL_SECRET' => 'SOME INTERNAL SECRET',
  'SERVER'=> 'example.com',
  'RETRY_TIMEOUT' => 10, // how often to check if a conversation is assigned
  'RETRY_ATTEMPTS' => 9, // num attempts to check if a conversation is assigned
  'SPAWN_PROCESS_TIMOUT_MS' => 1000
];
/*
Config properties explained:
===== RETRY_TIMEOUT & RETRY_ATTEMPTS =====
The background checking to see if a conversation is assigned.
Be mindful of PHP's max_execution_time. Our web host limits this
to 90 seconds, so we are checking every 10 seconds 9 times:
'RETRY_TIMEOUT' => 10,
'RETRY_ATTEMPTS' => 9,

===== SPAWN_PROCESS_TIMOUT_MS =====
The Kustomer webhook calling our notification web service expects a response
within a few seconds, otherwise it retries thinking that the server wasn't
reached. The webhook does this about 15 times before giving up, resulting
in multiple notifications!

But, our code execution takes a while, since we need to wait for
state change on Kustomer. (PHP doesn't have asynchronous functions, and sending
a response only occurs after all script execution has completed.)

In order to satisfy Kustomer's webhook (i.e. quick response) index.php offloads
long running code to a child process, using a "fire-and-forget" HTTP CURL
request to processor.php.
When we initiate this request, we need to introduce a small timeout to give
CURL enough time to make the request properly before index.php terminates.
This timeout should be something between 100ms and 1000ms, depending on
the performance and latency of the web host.

  */
