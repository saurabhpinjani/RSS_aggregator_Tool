from elasticsearch import Elasticsearch
import os

def title_hash_file_update(new_title_hash): # adds new titles to the hash table

	cwd = os.getcwd()
	
	with open(cwd+"/Data_Files/"+'arxiv_title_hash_file.txt','a') as file_obj:
		for key in new_title_hash.keys():
			title_parts= key.split("\n")
			title_str=""
			for i in title_parts:
				title_str=title_str+i+" "

			file_obj.write( title_str[:-1]+"|" + str(new_title_hash[key])+"\n")
			
	print len(new_title_hash)," new article(s) added"	
new_title_hash={}
title_hash={}
es = Elasticsearch(['http://localhost:9200'])
res = es.search(index="arxiv_feed", doc_type="feed",size=2000)
res=res['hits']['hits']

for entry in res:
	 # this part of code removes all the new line characters that might be present between the title as in teh fees and replaces them with spaces
	entry_id=entry['_id']
	entry=entry['_source']
	print entry_id
	print entry.keys()
	title_parts= entry['title'].encode('utf-8').split("\n")
	title_str=""
	
	for i in title_parts:
		title_str=title_str+i+" "
	title_str=title_str[:-1]

	if(title_hash.has_key(title_str )== False): # if this article is not already in the database
		print 'title_str',title_str
		
		new_title_hash[title_str]=entry_id
			
title_hash_file_update(new_title_hash)
cwd =os.getcwd()



