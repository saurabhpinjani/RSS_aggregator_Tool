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

def classified_feed_meta_setup(journals_array):
	
	print("Setup \n")
	for journal in journals_array:
		try:
			res = es.get(index="classified_feed", doc_type=journal, id= 0)
			print(journal+" is already in meta data. So it is ignored \n")
		except:
			res = es.index(index="classified_feed", doc_type=journal, id= 0, body={"all_titles": {}, "classified_titles" : {} })
			print(journal+" is added to meta data \n") 



def classified_feed_db_setup(journals_array):
	total_new = 0
	for journal in journals_array:
		#get meta data of that particular journal
		res_class = es.get(index="classified_feed", doc_type=journal, id= 0)
		all_titles = res_class['_source']['all_titles']
		#get the rss_feed DB journal by journal
		res=es.search(index="rss_feed", doc_type=journal, size=5000)
		articles = res['hits']['hits']
		new_articles=0
		for article in articles:
			article_title = article['_source']['title']
			#add to db only if title not in hash
			if article_title not in all_titles.values():
				new_id = len(all_titles)+1
				all_titles[new_id] = article_title
				push_data =es.index(index="classified_feed", doc_type=journal, id = new_id, body=article['_source'])
				new_articles = new_articles+1
		print( str(new_articles)+" new articles from "+ journal+" added to DB \n")
		res_class['_source']['all_titles'] = all_titles
		push_titles = es.index(index="classified_feed", doc_type=journal, id= 0, body=res_class['_source'])
		total_new = total_new + new_articles

	print("In total, "+str(total_new)+" articles added to DB from various journals \n")

#list of jounals in an array
print("=======================================================================================\n")
print(str(datetime.now())+'\n')
f = open(os.getcwd()+'/RSS_urls/journals.txt','r')
journals_array = []
for line in f:
	line=line.lower()
	line=line.strip('\n');
	journals_array.append(line)

f.close()

classified_feed_meta_setup(journals_array)
classified_feed_db_setup(journals_array)