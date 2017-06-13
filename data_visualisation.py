import os

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
		[url,impact_fact,journal]=url.split('|')
		journal= journal.lower()
		impact_fact=float(impact_fact)
		feed= rss_url_read(url)
		print feed[0].keys()
		


file_list ='rss_feeds.txt'
	
read_feed_from_file(file_list)
		
