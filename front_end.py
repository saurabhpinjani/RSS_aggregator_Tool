import Tkinter
import tkMessageBox
import os
from datetime import datetime
from elasticsearch import Elasticsearch
from numpy import *
from fractions import Fraction
import sys
import numpy as np
reload(sys)
sys.setdefaultencoding('utf8')

# import io, json
# with io.open('data.txt', 'w', encoding='utf-8') as f:
#    f.write(json.dumps(data, ensure_ascii=False))


os.system('sudo service elasticsearch start')
es = Elasticsearch(['http://localhost:9200'])
root = Tkinter.Tk()
root.wm_title("ElasticSearch")

Index_var = Tkinter.StringVar()
Type_var = Tkinter.StringVar()
Id_var = Tkinter.IntVar()
Push_var = Tkinter.StringVar()
Seach_var = Tkinter.StringVar()
Seach_var_2 = Tkinter.StringVar()
Body_var = Tkinter.StringVar()
Search_out_var = Tkinter.StringVar()
auth_check_var = Tkinter.IntVar()
url_check_var = Tkinter.IntVar()
show_all_check_var = Tkinter.IntVar()

def localhost_push():
	doc = {'message':Body_var.get()}
	res = es.index(index=Index_var.get(), doc_type=Type_var.get(), id=Id_var.get(), body=doc)
	tkMessageBox.showinfo( "Response",Index_var.get()+" "+Type_var.get()+" "+Body_var.get())
	Index_var.set("")
	Type_var.set("")
	Body_var.set("")
	Id_var.set("")

def localhost_search():
	res = es.search(body={"query": {"match": {"_all": {"query":Seach_var.get(),"operator":"and"} }} })
	if res['hits']['total']!=0:	
		x = res['hits']['hits']
		out = ""
		for a in x:
			out = out + "index : "+ str(a['_index'])+"\n"
			out = out + "type : "+ str(a['_type'])+"\n"
			out = out + "ID : "+str(a['_id'])+"\n"
			field_list = []
			for field in a['_source']:
				field_list.append(field)
			for i in field_list:
				out = out + str(i)+ " : "+str(a['_source'][i])+"\n"
			out = out + "___________\n" 
		Search_out_var.set(out)
	else:
		out = "error 404, not found"
		Search_out_var.set("error 404, not found")
	#tkMessageBox.showinfo("Response","Output for query "+ Seach_var.get() + "\n\n" + Search_out_var.get())

	search_out_list = out.split('\n')
	mylist.delete(0,mylist.size())
	for line in search_out_list:
   		mylist.insert(Tkinter.END, line)
	Seach_var.set("")

lab_but_h = float(Fraction(1,22))
lab_but_w = float(Fraction(1,9))

index_lab_x = float(Fraction(1,3))# 1/3
index_lab_y = float(Fraction(2,32)) #2/32
type_lab_x = index_lab_x
type_lab_y = index_lab_y + lab_but_h
id_lab_x = index_lab_x
id_lab_y = type_lab_y + lab_but_h
body_lab_x = index_lab_x
body_lab_y = id_lab_y + lab_but_h

index_ent_x = index_lab_x + lab_but_w
index_ent_y = index_lab_y
type_ent_x = type_lab_x + lab_but_w
type_ent_y = type_lab_y
id_ent_x = id_lab_x + lab_but_w
id_ent_y = id_lab_y
body_ent_x = body_lab_x + lab_but_w
body_ent_y = body_lab_y

push_but_x = index_lab_x + lab_but_w
push_but_y = body_ent_y + lab_but_h

search_lab_x = push_but_x- lab_but_w
search_lab_y = push_but_y + lab_but_h + lab_but_h
search_ent_x = search_lab_x + lab_but_w
search_ent_y = search_lab_y
auth_check_x = push_but_x
auth_check_y = search_ent_y + lab_but_h
show_all_check_x = auth_check_x - lab_but_w
show_all_check_y = auth_check_y
url_check_x = auth_check_x + lab_but_w
url_check_y = auth_check_y

search_but_x = push_but_x
search_but_y = search_ent_y+2*lab_but_h



msg_frame = Tkinter.Frame(root)
msg_frame.place(relx = 0, rely=0.4, height=600, relwidth=1)

i_o_frame = Tkinter.Frame(root, bg='grey')
i_o_frame.place(relx = 0, rely=0, relheight=0.4, relwidth=1)
#scrollbar = Tkinter.Scrollbar(msg_frame, orient =Tkinter.VERTICAL, command= )
#scrollbar.pack( side = Tkinter.RIGHT, fill=Tkinter.Y)
y_scrollbar = Tkinter.Scrollbar(msg_frame)
y_scrollbar.pack( side = Tkinter.RIGHT, fill=Tkinter.Y )
x_scrollbar = Tkinter.Scrollbar(msg_frame,orient =Tkinter.HORIZONTAL)
x_scrollbar.pack( side = Tkinter.BOTTOM, fill=Tkinter.X )

Submit_button = Tkinter.Button(i_o_frame, text="PUSH!", command = localhost_push)
Index_label = Tkinter.Label(i_o_frame, text="Index")
Type_label = Tkinter.Label(i_o_frame, text="Type")
Id_label = Tkinter.Label(i_o_frame, text="ID")
Body_label = Tkinter.Label(i_o_frame, text="Body")

mylist = Tkinter.Listbox(msg_frame, height=50,yscrollcommand = y_scrollbar.set, xscrollcommand = x_scrollbar.set, selectmode=Tkinter.EXTENDED)

mylist.pack(fill = Tkinter.BOTH)
y_scrollbar.config( command = mylist.yview )
x_scrollbar.config( command = mylist.xview )
#msg_label = Tkinter.Message(msg_frame,textvariable=Search_out_var)
#msg_label.pack()

Index_entry = Tkinter.Entry(i_o_frame, textvariable=Index_var)
Type_entry = Tkinter.Entry(i_o_frame, textvariable=Type_var)
Id_entry = Tkinter.Entry(i_o_frame, textvariable=Id_var)
Body_entry = Tkinter.Entry(i_o_frame, textvariable=Body_var)

auth_check = Tkinter.Checkbutton(i_o_frame, text = "Author", variable = auth_check_var, onvalue = 1, offvalue = 0)
url_check = Tkinter.Checkbutton(i_o_frame, text = "URL", variable = url_check_var, onvalue = 1, offvalue = 0)
show_all_check = Tkinter.Checkbutton(i_o_frame, text="show all", variable = show_all_check_var, onvalue = 1, offvalue = 0)


Type_label.place(relx=type_lab_x, rely=type_lab_y,relheight=lab_but_h, relwidth=lab_but_w)
Index_label.place(relx=index_lab_x, rely=index_lab_y,relheight=lab_but_h, relwidth=lab_but_w)
Id_label.place(relx=id_lab_x, rely=id_lab_y,relheight=lab_but_h, relwidth=lab_but_w)
Body_label.place(relx=body_lab_x, rely=body_lab_y,relheight=lab_but_h, relwidth=lab_but_w)
Submit_button.place(relx=push_but_x, rely=push_but_y,relheight=lab_but_h, relwidth=lab_but_w)

Index_entry.place(relx=index_ent_x, rely=index_ent_y,relheight=lab_but_h, relwidth=2*lab_but_w)
Type_entry.place(relx=type_ent_x, rely=type_ent_y,relheight=lab_but_h, relwidth=2*lab_but_w)
Id_entry.place(relx=id_ent_x, rely=id_ent_y,relheight=lab_but_h, relwidth=2*lab_but_w)
Body_entry.place(relx=body_ent_x, rely=body_ent_y,relheight=lab_but_h, relwidth=2*lab_but_w)

Search_label = Tkinter.Label(i_o_frame, text="Search for?")
Search_button = Tkinter.Button(i_o_frame, text="Search!", command = localhost_search)
Search_entry = Tkinter.Entry(i_o_frame,textvariable = Seach_var)

Search_label.place(relx=search_lab_x, rely=search_lab_y,relheight=lab_but_h, relwidth=lab_but_w)
Search_entry.place(relx=search_ent_x, rely=search_ent_y,relheight=lab_but_h, relwidth=2*lab_but_w)
Search_button.place(relx=search_but_x, rely=search_but_y,relheight=lab_but_h, relwidth=lab_but_w)

auth_check.place(relx = auth_check_x, rely = auth_check_y, relheight=lab_but_h, relwidth=lab_but_w)
url_check.place(relx = url_check_x, rely = url_check_y, relheight=lab_but_h, relwidth=lab_but_w)
show_all_check.place(relx = show_all_check_x, rely = show_all_check_y, relheight=lab_but_h, relwidth=lab_but_w)

root.mainloop()
