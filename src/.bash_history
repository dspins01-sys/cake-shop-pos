ping wa-service
exit
apt-get update && apt-get install curl -y
exit
curl http://172.20.0.3:3000/health
exit
curl http://cake-wa:3000/health
curl http://wa-service:3000/health
exit
