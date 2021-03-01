#!/bin/bash

CHANGED=$(git diff HEAD --name-only -- ../js)
LINTFILES=""

for file in $CHANGED
do
  LINTFILES+=" --file=../../$file"
done

grunt eslint:file --quiet $LINTFILES
