sudo tail -n20 /var/log/gearman.log

---
/etc/supervisor$ sudo nano supervisord.conf

[supervisord]
logfile=/var/log/supervisor/supervisord.log ; (main log file;default $CWD/supervisord.log)
user = root
pidfile=/var/run/supervisord.pid ; (supervisord pidfile;default supervisord.pid)
childlogdir=/var/log/supervisor            ; ('AUTO' child log dir, default $TEMP)
minfds=20000
; the below section must remain in the config file for RPC
; (supervisorctl/web interface) to work, additional interfaces may be
; added by defining them in separate rpcinterface: sections

---

https://superuser.com/questions/595989/ssh-through-a-router-without-port-forwarding

"I have a linux server, and I want to put it in a home network behind a router. I need to ssh to this server sometime from outside, but I don't want to set up port forwarding because I don't have access to the router, and I don't know the ip of the router either."

"What you would want to do is ssh FROM your "linux server" TO something on the outside, such as "my_other_server" or something else both servers can get to.

You would use ssh remote port forwarding.
[user@linux_server]$ ssh -R8022:localhost:22 my_other_server.com
Explaination: Connect to my_other_server and open port 8022 there which will forward back to me on port 22.

From my_other_server.com you will be able to ssh to localhost on port 8022, and have your traffic forwarded to linux_server piggybacking on the linux_server -> my_other_server tunnel [user@linux_server]$ ssh -p8022 localhost
Explaination: Connect to myself on port 8022 which is forwarded to linux_server

If you have problems with the initial linux_server -> my_other_server tunnel dropping out, you could make a script to keep it open, adjust the keepalive settings, or use autossh."

---

https://www.techrepublic.com/article/how-to-use-local-and-remote-ssh-port-forwarding/
 Before you do this, however, you need to add an option to the /etc/ssh/sshd_config file. Open that file in your editor of choice and add the following line at the bottom:

GatewayPorts yes

---

ssh -fN -R 10022:imagen:22 nick@stackr.ca
