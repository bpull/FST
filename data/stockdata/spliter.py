fp = open("justtick.txt","r")
count = 0
which = 0
fn = "justtick"+str(which)+".txt"
fp2 = open(fn,"w")
for line in fp:
    if count == 50:
        which += 1
        count = 0
        fn = "justtick"+str(which)+".txt"
        fp2.close()
        fp2 = open(fn,"w")

    count +=1
    fp2.write(line)
