@echo off
call mvn -q package -DskipTests
java -jar target\club-platform-1.0-jar-with-dependencies.jar
pause
