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


def get_journal_list():
	# Fetches the list of journals from the txt file containing the list
	file_obj= open(os.getcwd() +'/RSS_urls/journals.txt','r')
	lines =file_obj.readlines()
	journal_list=[]
	for line in lines:
		journal_list.append(line.strip('\n').lower())
	file_obj.close()
	

	return journal_list	

def es_database_setup():
	#this function is used to setup the database if it is cleared
	# it adds the indices "rss_feed" and "users"
	journal_list=get_journal_list()
	count ={}
	for journal in journal_list:
		count[journal]=int(0)
	
	es.index(index="rss_feed", doc_type="info", id=0, body={"count": count}) # count stores number of articles of each journal in the database
	es.index(index="users", doc_type="metadata", id=0, body={"count": 0})# count stores number of registed user

	cwd = os.getcwd()
	file_obj=open(cwd+"/Data_Files/"+'title_hash_file.txt','w') # clears the file containing the title of the journal in the database
	file_obj.close() 

def add_journal_to_count(journal):
	res=es.get(index="rss_feed", doc_type="info", id=0)
	count =res['_source']['count']
	count[journal]=0
	es.index(index="rss_feed", doc_type="info", id=0, body={"count": count})
	return count
	
def es_clear_user_database(): #This function is used to remove all the users from the database
	es.indices.delete(index="users", ignore=[400, 404])
	es.index(index="users", doc_type="metadata", id=0, body={"count": 0})# count stores number of registed user
	print "User database cleared"

def es_clear_feed_database(): #This function is used to remove all the feeds from the database
	
	es.indices.delete(index="rss_feed", ignore=[400, 404])
	cwd = os.getcwd()
	file_obj=open(cwd+"/Data_Files/"+'title_hash_file.txt','w')
	file_obj.close() 

	journal_list=get_journal_list()
	count ={}
	for journal in journal_list:
		count[journal]=int(0)
	

	es.index(index="rss_feed", doc_type="info", id=0, body={"count": count}) # count stores number of articles of each journal in the database
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

def get_material_list(): # it fetches a list of the compounds that occur in the database
	cwd = os.getcwd()
	
	return read_list_from_file(cwd+"/php_data/public_html/materials/"+'compounds_found.txt')		

def get_property_dict(): # gets a list of the properties.
	
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
# function determines if a given article is in the array of titles. Primarily used to create the others column in the materials table
	
	for i in range(len(res_matrix_row)-2):
		if((id in res_matrix_row[i])==True):
			return True
	return False

def material_table_update(): # function used to build the material table
	print "Populating Material Table..."
	journal_list=get_journal_list()
	material_list =get_material_list()
	property_dict=get_property_dict()
	

	for journal in journal_list:
		# a table is created for each journal
		count_matrix = [[[0,0] for y in range(9)] for x in range(len(material_list))] # the second element is currently unused.can be later used to keep track of read/ unread articles
		res_matrix =[[[] for y in range(9)] for x in range(len(material_list))]
		i=0
		for material in material_list:
			j=0

			for property in sorted(property_dict.keys()):

				aggr_res=[]

				for sub_property in property_dict[property]:
					Search_var = material + " "+ sub_property  # a search string with material name and the subproperty is used as a query
					res = es.search(index="rss_feed",doc_type=journal,body={"query": {"match": {"_all": {"query":Search_var,"operator":"and"} }} },size=1000)
					
					for item in res['hits']['hits']:

						if( int(item['_id']) not in aggr_res):#if the article has not been previously added due to some other subproperty
							aggr_res.append(int(item['_id']))
							count_matrix[i][j][0] = count_matrix[i][j][0]+1
							
						
				res_matrix[i][j]=aggr_res
				j=j+1
			res = es.search(index="rss_feed",doc_type=journal,body={"query": {"match": {"_all": {"query":material} }} },size=1000)
			count_matrix[i][len(property_dict)+1][0] = res['hits']['total'] # adding the value of "total field" in the table
			
			for item in res['hits']['hits']:
				
				res_matrix[i][len(property_dict)+1].append(item['_id']) # adding articles in the total field
				if(test_item_presence(int(item['_id']),res_matrix[i])== False): # if article wasn't classified in any field
					res_matrix[i][len(property_dict)].append(int(item['_id']))  # adding to 'others' field
					count_matrix[i][len(property_dict)][0] = count_matrix[i][len(property_dict)][0]+1
						
			
			res_matrix[i].append(material) # adding the name of the material to each row
			count_matrix[i].append(material)
			i=i+1
		
		
		file_obj= open(os.getcwd()+"/php_data/public_html/materials/"+journal+"_material_table_counts.json",'w') # writing the table for a given journal to a json file
		file_obj.write(json.dumps(count_matrix))
		file_obj.close()
		file_obj= open(os.getcwd()+"/php_data/public_html/materials/"+journal+"_material_table_results.json",'w')
		file_obj.write(json.dumps(res_matrix))
		file_obj.close()

		


def es_database_populate():
	journal_list =get_journal_list()
	count =es.get(index="rss_feed",doc_type='info',id=0)['_source']['count'] # gets a dictionary with new elements in each

	for journal in journal_list:         # if a new jounal if added to the file.
		if(journal not in count.keys()):
			count[journal]=0

	aggr_feed= rss_url_read.read_feed() # the feed is read from all the urls specifed a file
	new_title_hash={}
	title_hash =fill_title_hash()
	
	comp_file = open(os.getcwd()+"/php_data/public_html/materials/compounds_found.txt","r")
	
	compounds_list=comp_file.readlines();	
	comp_file.close()
	comp_file = open(os.getcwd()+"/php_data/public_html/materials/compounds_found.txt","w")
	for entry in aggr_feed:
		 # this part of code removes all the new line characters that might be present between the title as in teh fees and replaces them with spaces
		title_parts= entry['title'].encode('utf-8').split("\n")
		title_str=""
		
		for i in title_parts:
			title_str=title_str+i+" "
		title_str=title_str[:-1]

		if(title_hash.has_key(title_str )== False): # if this article is not already in the database
			print 'title_str',title_str
			compounds = extractTags(entry) # this function extracts all the compounds that exist within the rss feed of this article
			if(entry['journal'] not in count.keys()):
				count=add_journal_to_count(entry['journal'])
			count[entry['journal']]=int(count[entry['journal']]) +1
			new_title_hash[title_str]=count[unicode(entry['journal'],'utf-8')]
			
			entry['compounds_list']=compounds
			journal_name =entry['journal']

			
			x = compounds.split(" ")
			for comp in x: # adding any newly encountered compounds to the global compount list
				if (not (comp in compounds_list)) and (len(comp)>3):
					compounds_list.append(comp)
					comp_file.write(comp+"\n")
			entry = json.dumps(entry, default=to_json)
			res = es.index(index="rss_feed", doc_type=journal_name, id=int(count[journal_name]), body=entry)
	comp_file.close()
	es.index(index="rss_feed", doc_type='info', id=0, body={"count":count})
	es.indices.refresh(index="rss_feed")
	title_hash_file_update(new_title_hash)
	
	comp_file.close()
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

	compounds = saveCompounds(str(complete))

	return compounds



chemDetectInit()


es_clear_feed_database()
es_database_setup()
#es_clear_user_database()
es_database_populate()
#print get_material_list()

#print get_property_dict().keys()

