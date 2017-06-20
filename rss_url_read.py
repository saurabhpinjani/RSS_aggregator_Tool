import feedparser
import os

def add_journal_impact_factor(feed,journal,impact_factor,publisher):
	for item in feed:
		item['journal']=journal
		item['impact_factor']=impact_factor
		item['publisher']=publisher
	
	return feed	


def rss_url_read(url):
	# Given the url of a particular website it reads the feed
	feed=feedparser.parse(url)
	
	entries=feed["items"]
	#print entries
	return entries



def read_urls_from_file(file_name):
	# Given the file name containing the urls it extracts the urls mentioned in the file 
	cwd = os.getcwd()
	with open(cwd+"/RSS_urls/"+file_name,'r') as file_obj:
		urls = file_obj.readlines()
	urls =[x.strip() for x in urls]
	return urls

def read_feed_from_file(file_name):
	# Given the file name containing the urls it extracts the urls mentioned in the file 
	url_list= read_urls_from_file(file_name)
	file_feed_list= []
	for url in url_list:
		[url,impact_fact,journal,publisher]=url.split('|')
		journal= journal.lower()
		impact_fact=float(impact_fact)
		feed= rss_url_read(url)
		feed=add_journal_impact_factor(feed,journal,impact_fact,publisher)
		file_feed_list= file_feed_list+ feed;
	
		
	
	return file_feed_list

def read_feed():
	# Reads and aggregates the feed from the entire set of url mentioned in the RSS_urls folder
	
	#file_list=['ACS_url.txt','AIP_url.txt','APS_url.txt','EL_url.txt','IOP_url.txt','nature_url.txt','RSC_url.txt','WI_url.txt']
	file_list =['rss_feeds.txt']
	full_feed=[]
	for file in file_list:
		file_feed=read_feed_from_file(file)
		full_feed=full_feed+file_feed

	
	return full_feed;


print rss_url_read("")
