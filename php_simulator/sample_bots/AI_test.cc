#include <stdio.h>
#include <sys/types.h>
#include <unistd.h>

int main(void)
{
	fprintf(stderr,"Bot 4 pid : %d\n",getpid());
	return 0;
}
