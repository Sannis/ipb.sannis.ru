try {
  var meryl = require('meryl');
} catch (e) {
  console.log("You should install Meryl before running this site. Try:");
  console.log("> npm install meryl");
  process.exit(1);
}

try {
  var static = require('node-static@0.5.2');
} catch (e) {
  console.log("You should install node-static@0.5.2 before running this site. Try:");
  console.log("> npm install node-static@0.5.2");
  process.exit(1);
}

// Configure node-static
var fileServer = new static.Server('../../../static.sannis.ru');


process.title = "IPB modifications site";

// TODO: Add daemonize code

require('http').createServer(
  meryl
  .handleNotFound(function (req, resp) {
      console.log("404");
      console.log(req);
      fileServer.serveFile('/404.html', 404, {}, req, resp);
    }
  )
  .handleError(function (req, resp) {
      console.log("500");
      console.log(req);
      fileServer.serveFile('/500.html', 500, {}, req, resp);
    }
  )
  .handle('GET /n', function (req, resp) {
      resp.send('Hello, world!');
    }
  )
  .handle('GET /', function (req, resp) {
      resp.send('Hello, root!');
    }
  )
  .cgi({debug: true})
).listen(12701);

