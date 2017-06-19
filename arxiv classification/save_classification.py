from elasticsearch import Elasticsearch
import os
ced =os.getcwd()
es = Elasticsearch(['http://localhost:9200'])
res = es.get(index="arxiv_feed", doc_type="info", id=0)

count=res['_source']['count']
marked_articles=res['_source']['marked_articles']

f=open(cwd+'/Data_Files/classified_articles.txt','r')
data= f.readlines()
f.close()

f=open('/Data_Files/classified_articles.txt','a')
no_already_marked=len(data)
for i in range(no_already_marked+1,marked_articles+1):
	res = es.get(index="arxiv_feed",doc_type="feed",id=i)
	category =res['_source']['category_choice']['category']
	f.write(str(i)+'|'+str(category))
f.close()
