'use strict';
const config = require("./config/config.json");
const fetch = require('node-fetch');
const http = require('http');
const {
  ConsoleLogColors
} = require("js-console-log-colors");
const out = new ConsoleLogColors();

const server = http.createServer(async (req, res) => {
  out.info("##### Client Request: " + req.method + " " + req.url);

  const url = new URL(req.url, "http://example.test");

  const res_json = {};
  if (req.method === "GET" || req.method === "POST") {
    const arrPath = url.pathname.split("/");
    switch (arrPath[1]) {
      case "notify": // https://localhost:9000/notify
        //if (arrPath[2] && arrPath[2] != '') {

        //set the response
        res.writeHead(200, {
          "Content-Type": "application/json"
        });

        res.write(JSON.stringify({}));
        res.end();
        return;
        //}

        break;
      default:
        break;
    }
  }


  console.log(404);
  res.writeHead(404);
  res.end();
});

server.listen(config.port, /*config.host,*/ () => {
  out.success(`Server is running on http://${config.host}:${config.port}`);
});


function doTelegramNotification(text = "") {

  const json_body = {
    "chat_id": config.TG_CHAT_ID,
    "text": "testy"
  };

  fetch(`https://api.telegram.org/bot${config.TG_BOT_KEY}/sendMessage`, {
      method: 'POST',
      body: JSON.stringify(json_body),
      headers: {
        'Content-Type': 'application/json'
      }
    }).then(res => res.json())
    .then((json) => {
      out.info(json);
    });

}
