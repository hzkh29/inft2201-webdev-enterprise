import http from "http";
import fs from "fs";
import jwt from "jsonwebtoken";

const JWT_SECRET = "SET_A_RANDOM_STRING_FOR_FULL_MARKS";

http
  .createServer((req, res) => {
    if (req.method === "GET") {
      res.writeHead(200, { "Content-Type": "text/plain" });
      res.end("Hello Apache!\n");

      return;
    }

    if (req.method === "POST") {
      if (req.url === "/login") {
        let body = "";
        req.on("data", (chunk) => {
          body += chunk;
        });
        req.on("end", () => {
          try {
            body = JSON.parse(body);

            // handle a login attempt

            // open up our "database" (actually a flat file called ./users.txt)
            // to see if there is a username/password combination that matches
            // body.username and body.password

            const fileData = fs.readFileSync("/usr/src/app/users.txt", "utf8");
            const lines = fileData.trim().split("\n");

            console.log(lines);

            let foundUser = null;

            // Loop for match checking the entered values in the fields
            for (let i = 0; i < lines.length; i++) {
              const parts = lines[i].split(",");

              // Will check each line, then it will splits by comma
              // then checks if the entered username matches the username
              // After that, once the username matches, it will store it
              // in the foundUser

              // parsing part

              const userId = parts[0];
              const username = parts[1];
              const password = parts[2];
              const role = parts[3];

              // matching part

              if (username === body.username) {
                foundUser = {
                  userId: userId,
                  username: username,
                  password: password,
                  role: role,
                };
              }
            }

            // return a 404 error if the username isn't found
            if (foundUser === null) {
              res.writeHead(404, { "Content-Type": "text/plain" });
              res.end("User not found");
              return;
            }

            // return a 401 error if the username is found but the password doesn't match

            if (foundUser.password !== body.password) {
              res.writeHead(401, { "Content-Type": "text/plain" });
              res.end("Invalid password");
              return;
            }

            // if the user if found

            res.writeHead(200, { "Content-Type": "application/json" });
            res.end(JSON.stringify(foundUser));
            return;

            // on success, return an encoded userId and role using your JWT_SECRET.
            // https://www.npmjs.com/package/jsonwebtoken
          } catch (err) {
            console.log(err);
            res.writeHead(500, { "Content-Type": "text/plain" });
            res.end("Server error\n");
          }
        });
      }

      return;
    }

    res.writeHead(404, { "Content-Type": "text/plain" });
    res.end("Not found\n");
  })
  .listen(8000);

console.log("listening on port 8000");
