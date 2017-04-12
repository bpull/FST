import subprocess,os
fp = open("justtick22.txt","r")
#import subprocess,os
#print subprocess.check_output(["python","hello.py"])
#fp = open("justtick9.txt","r")

for line in fp:
    for i in range(2007,2017,1):
        newdir = line.strip()+"/"+str(i)+"-data/"
        if not os.path.exists(newdir):
            os.makedirs(newdir)
        for j in range(1,13,1):
            if not os.path.exists(newdir+str(j)):
                try:
                    print "writing to "+newdir+str(j)
                    os.system("python tradeCalendar2.py " +str(j)+" "+str(i)+" | python stockData.py "+line.strip()+" > "+newdir+str(j))
                except:
                    pass
