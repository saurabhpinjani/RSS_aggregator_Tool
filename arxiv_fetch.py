from datetime import datetime
from elasticsearch import Elasticsearch
import rss_url_read
import json
import time
import os
from chemical_detect import *
import sys
import numpy as np
import feedparser
import os.path

reload(sys)
sys.setdefaultencoding('utf8')
es = Elasticsearch(['http://localhost:9200']) # to create the elastic search object

def to_json(python_object): #this function is used to serialise rss feeds before sending them to the elastic search database
    if isinstance(python_object, time.struct_time):
        return {'__class__': 'time.asctime',
                '__value__': time.asctime(python_object)}

    raise TypeError(repr(python_object) + ' is not JSON serializable')



def es_database_setup():
	#this function is used to setup the database if it is cleared
	# it adds the indices "rss_feed" and "users"

	count =0
	no_articles_marked=0
	
	
	es.index(index="arxiv_feed", doc_type="info", id=0, body={"count": count , "marked_articles":no_articles_marked}) # count stores number of articles of each journal in the database
	
	cwd = os.getcwd()
	file_obj=open(cwd+"/Data_Files/"+'title_hash_file.txt','w') # clears the file containing the title of the journal in the database
	file_obj.close() 
	

def es_clear_feed_database(): #This function is used to remove all the feeds from the database
	
	es.indices.delete(index="arxiv_feed", ignore=[400, 404])
	cwd = os.getcwd()
	file_obj=open(cwd+"/Data_Files/"+'title_hash_file.txt','w')
	file_obj.close() 

	es_database_setup()

	
	print "RSS Feed database cleared"

def fill_title_hash(): # creates a hash table of the titles of all the articles in the database

	cwd = os.getcwd()
	title_hash={}
	with open(cwd+"/Data_Files/"+'title_hash_file.txt','r') as file_obj:
		lines=file_obj.readlines()
	for line in lines:	
		line=line.split('|')
		if(len(line)==2):
			title_hash[line[0]]=line[1]

	return title_hash

def title_hash_file_update(new_title_hash): # adds new titles to the hash table

	cwd = os.getcwd()
	
	with open(cwd+"/Data_Files/"+'title_hash_file.txt','a') as file_obj:
		for key in new_title_hash.keys():
			title_parts= key.split("\n")
			title_str=""
			for i in title_parts:
				title_str=title_str+i+" "

			file_obj.write( title_str[:-1]+"|" + str(new_title_hash[key])+"\n")
			
	print len(new_title_hash)," new article(s) added"		

def read_list_from_file(file): # general finction to read files
	list_obj=[]
	with open(file,'r') as file_obj:
		lines=file_obj.readlines()
	for line in lines:
		list_obj.append(line.strip("\n"))
	return list_obj

def es_database_populate():
	
	metadata =es.get(index="arxiv_feed",doc_type='info',id=0)['_source'] # gets number of articles in the database
	count = metadata['count']
	
	aggr_feed= rss_url_read.rss_url_read('http://arxiv.org/rss/cond-mat') # the feed is read from all the urls specifed a file
	new_title_hash={}
	title_hash =fill_title_hash()
	
	
	for entry in aggr_feed:
		 # this part of code removes all the new line characters that might be present between the title as in teh fees and replaces them with spaces
		title_parts= entry['title'].encode('utf-8').split("\n")
		title_str=""
		
		for i in title_parts:
			title_str=title_str+i+" "
		title_str=title_str[:-1]

		if(title_hash.has_key(title_str )== False): # if this article is not already in the database
			print 'title_str',title_str
			count =count +1
			new_title_hash[title_str]=count
			
		
			entry = json.dumps(entry, default=to_json)
			res = es.index(index="arxiv_feed", doc_type="feed", id=count, body=entry)
	
	metadata['count']=count
	es.index(index="arxiv_feed", doc_type='info', id=0,body=metadata )
	es.indices.refresh(index="rss_feed")
	title_hash_file_update(new_title_hash)
	




#es_database_setup()
#es_clear_feed_database()


es_database_populate()




