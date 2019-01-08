echo "Queue report"
(echo status ; sleep 0.1) | nc 127.0.0.1 4730
(echo workers ; sleep 0.1) | nc 127.0.0.1 4730
echo "Queue report completed."
