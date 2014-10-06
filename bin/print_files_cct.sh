#!/bin/bash

find . -name "*.php" -o -name "*.inc" | xargs grep -l "_?cct([^\'\"].*[^\'\"])" | sort
