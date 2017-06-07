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

reload(sys)
sys.setdefaultencoding('utf8')

es = Elasticsearch(['http://134.226.113.181:9200'])

def to_json(python_object):
    if isinstance(python_object, time.struct_time):
        return {'__class__': 'time.asctime',
                '__value__': time.asctime(python_object)}

    raise TypeError(repr(python_object) + ' is not JSON serializable')


def get_journal_list():

	file_obj= open(os.getcwd() +'/RSS_urls/journals.txt','r')
	lines =file_obj.readlines()
	journal_list=[]
	for line in lines:
		journal_list.append(line.strip('\n').lower())
	file_obj.close()
	

	return journal_list	

def es_database_setup():
	journal_list=get_journal_list()
	count ={}
	for journal in journal_list:
		count[journal]=int(0)
	print count
	es.index(index="rss_feed", doc_type="info", id=0, body={"count": count})

	cwd = os.getcwd()
	file_obj=open(cwd+"/Data_Files/"+'title_hash_file.txt','w') 
	file_obj.close() 
	file_obj= open(os.getcwd()+"/php_data/public_html/materials/material_table_counts.json",'w')
	file_obj.close()
	file_obj= open(os.getcwd()+"/php_data/public_html/materials/material_table_results.json",'w')
	file_obj.close()

def es_clear_database():
	
	es.indices.delete(index='_all', ignore=[400, 404])
	
	cwd = os.getcwd()

	file_obj=open(cwd+"/Data_Files/"+'title_hash_file.txt','w')
	file_obj.close() 
	file_obj= open(os.getcwd()+"/php_data/public_html/materials/material_table_counts.json",'w')
	file_obj.close()
	file_obj= open(os.getcwd()+"/php_data/public_html/materials/material_table_results.json",'w')
	file_obj.close()
	print "Database cleared"

def fill_title_hash():
	cwd = os.getcwd()
	title_hash={}
	with open(cwd+"/Data_Files/"+'title_hash_file.txt','r') as file_obj:
		lines=file_obj.readlines()
	for line in lines:	
		line=line.split('|')
		if(len(line)==2):
			title_hash[line[0]]=line[1]

	return title_hash

def title_hash_file_update(new_title_hash):

	cwd = os.getcwd()
	
	with open(cwd+"/Data_Files/"+'title_hash_file.txt','a') as file_obj:
		for key in new_title_hash.keys():
			title_parts= key.split("\n")
			title_str=""
			for i in title_parts:
				title_str=title_str+i+" "

			file_obj.write( title_str[:-1]+"|" + str(new_title_hash[key])+"\n")
			
	print len(new_title_hash)," new article(s) added"		

def read_list_from_file(file):
	list_obj=[]
	with open(file,'r') as file_obj:
		lines=file_obj.readlines()
	for line in lines:
		list_obj.append(line)
	return list_obj

def get_material_list():
	cwd = os.getcwd()
	
	return read_list_from_file(cwd+"/php_data/public_html/materials/"+'2D-List.txt')		

def get_property_dict():
	
	cwd = os.getcwd()
	prop_dict={}

	prop_dict['electric']=read_list_from_file(cwd+"/php_data/public_html/properties/"+'electric.txt')
	prop_dict['electronic_structure']=read_list_from_file(cwd+"/php_data/public_html/properties/"+'electronic_structure.txt')
	prop_dict['magnetism']=read_list_from_file(cwd+"/php_data/public_html/properties/"+'magnetism.txt')
	prop_dict['mechanical']=read_list_from_file(cwd+"/php_data/public_html/properties/"+'mechanical.txt')
	prop_dict['optical']=read_list_from_file(cwd+"/php_data/public_html/properties/"+'optical.txt')
	prop_dict['thermal']=read_list_from_file(cwd+"/php_data/public_html/properties/"+'thermal.txt')
	prop_dict['transport']=read_list_from_file(cwd+"/php_data/public_html/properties/"+'transport.txt')

	return prop_dict


def test_item_presence(id,res_matrix_row):
	
	for i in range(7):
		if((id in res_matrix_row[i])==True):
			return True
	return False

def material_table_update():
	print "Populating Material Table..."
	journal_list=get_journal_list()
	material_list =get_material_list()
	property_dict=get_property_dict()
	
	for journal in journal_list:
		count_matrix = [[[0,0] for y in range(9)] for x in range(len(material_list))]
		res_matrix =[[[] for y in range(9)] for x in range(len(material_list))]
		i=0
		for material in material_list:
			j=0
			for property in property_dict.keys():
				aggr_res=[]

				for sub_property in property_dict[property]:
					Search_var = material + " "+ sub_property
					res = es.search(index="rss_feed",doc_type=journal,body={"query": {"match": {"_all": {"query":Search_var,"operator":"and"} }} },size=1000)

					for item in res['hits']['hits']:
						if( int(item['_id']) not in aggr_res):
							aggr_res.append(int(item['_id']))
							count_matrix[i][j][0] = count_matrix[i][j][0]+1
							if(item['_source']['read_yet']=="brahmavishnu"):
								count_matrix[i][j][1]= count_matrix[i][j][1] +1
						
				res_matrix[i][j]=aggr_res
				j=j+1
			res = es.search(index="rss_feed",doc_type=journal,body={"query": {"match": {"_all": {"query":material} }} },size=1000)
			count_matrix[i][8][0] = res['hits']['total']
			
			for item in res['hits']['hits']:
				
				res_matrix[i][8].append(item['_id'])
				if(test_item_presence(int(item['_id']),res_matrix[i])== False):
					res_matrix[i][7].append(int(item['_id']))
					count_matrix[i][7][0] = count_matrix[i][7][0]+1
					if(item['_source']['read_yet']=="brahmavishnu"):
						count_matrix[i][7][1] = count_matrix[i][7][1]+1
						count_matrix[i][8][1] = count_matrix[i][8][1]+1
				else:
					if(item['_source']['read_yet']=="brahmavishnu"):
						count_matrix[i][8][1] = count_matrix[i][8][1]+1
		
			res_matrix[i].append(material)
			count_matrix[i].append(material)
			i=i+1
		#print " Sorting Entries of Material Table...."	
		#count_matrix, res_matrix = zip(*sorted(zip(count_matrix, res_matrix),key=lambda x: x[0][8][0],reverse=True))
		
		file_obj= open(os.getcwd()+"/php_data/public_html/materials/"+journal+"_material_table_counts.json",'w')
		file_obj.write(json.dumps(count_matrix))
		file_obj.close()
		file_obj= open(os.getcwd()+"/php_data/public_html/materials/"+journal+"_material_table_results.json",'w')
		file_obj.write(json.dumps(res_matrix))
		file_obj.close()

		


def es_database_populate():

	count =es.get(index="rss_feed",doc_type='info',id=0)['_source']['count']
	print 'count',count
	aggr_feed= rss_url_read.read_feed()#rss_url_read('http://feeds.nature.com/nmat/rss/aop')
	new_title_hash={}
	title_hash =fill_title_hash()	
	for entry in aggr_feed:
		
		title_parts= entry['title'].encode('utf-8').split("\n")
		title_str=""
		compounds = extractTags(entry)
		for i in title_parts:
			title_str=title_str+i+" "
		title_str=title_str[:-1]
		if(title_hash.has_key(title_str )== False):
			print 'title_str',title_str

			count[entry['journal']]=int(count[entry['journal']]) +1
			new_title_hash[title_str]=count[unicode(entry['journal'],'utf-8')]
			entry['read_yet']="brahmavishnu" # "brahmavishnu" is used to indicate false
			entry['compounds_list']=compounds
			journal_name =entry['journal']

			entry = json.dumps(entry, default=to_json)
			res = es.index(index="rss_feed", doc_type=journal_name, id=int(count[journal_name]), body=entry)
	
	es.index(index="rss_feed", doc_type='info', id=0, body={"count":count})
	es.indices.refresh(index="rss_feed")
	title_hash_file_update(new_title_hash)
	material_table_update()

def extractTags(x):
	complete = ""
	dic = feedparser.FeedParserDict()
	lis = [1,2]
	lis_type = type(lis)
	dic_type = type(dic)
	values = x.values()
	for value in values:
		value_type = type(value)
		if(value_type == type(dic)):
			values.extend(value.values())
		elif(value_type == type(lis)):
			values.extend(value[0].values())
		else:
			complete = complete +"\n , " + str(value)

	# print(complete)
	compounds = saveCompounds(str(complete))

	return compounds


#get_journal_list()

chemDetectInit()
es_clear_database()
es_database_setup()
es_database_populate()
#material_table_update()



#for hit in res['hits']['hits']:
#    print("%(author)s" % hit["_source"])
