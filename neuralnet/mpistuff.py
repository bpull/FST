import nettryscale
import numpy
from mpi4py import MPI
comm = MPI.COMM_WORLD
rank = comm.Get_rank()
size = comm.Get_size()

#number of possible net combinations
numcomboes = 16000

if rank == 0:
    #initialize buf with all the combinations of values
    #20*20*20*2 = 16000 total combinations
    count = 0;
    sendbuf = numpy.ndarray(shape=(numcomboes, 4))
    print(sendbuf.shape)
    for i in range(1, 21):
        ii = i * 25
        for j in range(1, 21):
            jj = j *25
            for k in range(1,21):
                kk = k * .001
                for l in range(0,2):
                    print(ii)
                    print(jj)
                    print(kk)
                    print(l)
                    sendbuf[count] = [ii, jj, kk, l]
                    print(sendbuf[count])
                    count += 1
                    print "finished %d" % count

else:
    #all instances must have a copy of the sendbuf but all ranks != 0 have none
    sendbuf = None

#initialiaze buffer to recieve arguments
recbuf = numpy.ndarray(shape=(numcomboes/size, 4))

#send out arguments
comm.Scatter(sendbuf, recbuf, root=0)

#holds lowest error percentage for the batch
lowerror = 1000000
lownumber = -1

#number of recieved comboes
numreccomboes = numcomboes/size
print(recbuf)

#each rank goes through their designated  parameters, trains the net, and tests
#returns the error of the test batch
for i in range(0, numreccomboes):
    arg1 = int(recbuf[i, 0])
    arg2 = int(recbuf[i, 1])
    arg3 = float(recbuf[i, 2])
    arg4 = int(recbuf[i, 3])
    print(arg1)
    print(arg2)
    print(arg3)
    print(arg4)
    result = nettryscale.run_net(arg1, arg2, arg3, arg4)
    #if lowest error then set that as the ranks lowest error
    if result < lowerror:
        lowerror = result
        lownumber = rank
        print("new low error!!!!")
        print(result)
        print("rank is " + str(rank))

#rank of smallest result after reduce

comm.Reduce(result, finalres, MPI.MINLOC)

print"final error and rank is "
print finalres
