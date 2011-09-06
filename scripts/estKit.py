#!/usr/bin/python

import sys
import zipfile

z = zipfile.ZipFile(sys.argv[1], "r")

if z.comment == "KIT":
	sys.exit(0)
else:
	sys.exit(1)

