#!/bin/bash

ssh -v $SERVER_USERNAME@$SERVER_IP

cd $SERVER_PATH

composer update