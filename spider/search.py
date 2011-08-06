#!/usr/bin/env python3
import sys
import re
import sqlite3
import lxml.html
import urllib.request
import urllib.error
from time import time
from pyparsing import Literal, Suppress, CharsNotIn, CaselessLiteral, \
	Word, dblQuotedString, alphanums, SkipTo, makeHTMLTags

add=[]
urls=[]
visited=[]
siteurl=False
MINADD=50

def save(db):
	global add

	db.executemany("insert into search(timestamp,title,url,content) values(?,?,?,?)",add)
	db.commit()
	add=[]

def continueparse(db):
	global urls
	global visited
	
	while len(urls) > 0:
		url=urls.pop()

		if url not in visited:
			visited.append(url)
			parse(db,url)
		
		if len(add) > MINADD:
			save(db)

def parse(db,url):
	global add
	global urls

	try:
		if not re.search('^http://',url):
			url=siteurl+"/"+url
			url="http://"+url.replace("//","/")

		request=urllib.request.Request(url)
		request.add_header('User-Agent', 'Flowgen/1.0 (http://floft.net)')
		page=urllib.request.urlopen(request)
		html=page.read().decode("utf-8")
		page.close()

		print("Notice: processing {}".format(url))

		#get urls
		linkOpenTag,linkCloseTag = makeHTMLTags("a")
		link = linkOpenTag + SkipTo(linkCloseTag).setResultsName("body") + linkCloseTag.suppress()

		for toks,strt,end in link.scanString(html):
			newurl=toks.startA.href

			if newurl not in urls and newurl not in visited:
				if re.search('^(/|http://'+siteurl+')',newurl) and not \
				   re.search('(jpg|png|flac|mp3|zip|pdf)$',newurl):
					urls.append(newurl)

		#get title
		try:
			title=re.search('<title>([^<]*)</title>',html).groups()[0]
		except:
			title="Untitled"
		
		#get text
		xml=lxml.html.document_fromstring(html.replace(">","> ").replace("<", " <"))
		text=xml.cssselect('body')[0].text_content().replace("\n"," ").strip()

		#add to database
		add.append([time(),title,url,text])
	except:
		print("Error: {} does not load".format(url))

if __name__ == '__main__':
	if len(sys.argv) == 3:
		url=sys.argv[1]
		siteurl=re.search('([^/]+)/?.*$',url.replace("http://","")).groups()[0]
		out=sys.argv[2]
		
		db=sqlite3.connect(out)
		db.execute("create virtual table search using fts4(timestamp float,title text,url text,content text)")
		
		visited.append(url)
		parse(db,url)
		continueparse(db)

		save(db)
		db.close()
	else:
		print("Usage: {} http://starturl output.sql".format(sys.argv[0]))
