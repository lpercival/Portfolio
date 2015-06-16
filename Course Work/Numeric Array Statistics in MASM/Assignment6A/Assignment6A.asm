TITLE Assignment 6A    (Assignment6A.asm)

; Author: Lisa Percival
; Course / Project ID: CS271/ Assignment 6A                 Date: 11/27/14
; Description: Get 10 integers from the user and store their numeric values in an array. Capture the integers using the readVal
; procedure, which uses the getString macro to get the entered string of digits, then converts the digit string to a number while
; validating that the input contains only digits and will fit in a 32-bit register. Once the 10 integers are in the array,
; calculate their sum and average. Then display the integers, their sum, and their average using the WriteVal procedure, which 
; converts the numeric values to a string of digits and invokes the displayString macro to do the actual printing.
; Implementation Notes: readVal and writeVal using lodsb and stosb, all procedure parameters are passed on the system stack,
; used registers are saved and restored by procedures and macros

INCLUDE Irvine32.inc

MAX_LENGTH = 10				; constant for the max length for the largest integer that fits in a 32-bit register
STRING_LENGTH = 15			; constant for the length of strings - max number of digits for integer + 5 to leave room for testing
NUM = 10					; constant for the number of integers to capture from the user, and the array size

;***********************************************************************
;Macro that uses Irvine's ReadString to get input from the user. Pass a memory location as the parameter
; Note: Taken mostly from lecture
;receives: input the place where the input should be stored, maxSize the allowed size of the input, prompt to display (address)
;returns: the length of the string in eax
;preconditions:  none
;registers changed: ecx, edx, eax (not pushed/popped because returns)
;***********************************************************************
getString	MACRO	input, maxSize, prompt
	push	ecx
	push	edx
	displayString	prompt
	mov		edx, input
	mov		ecx, maxSize-1		; expected length: leave room for 0 byte added by ReadString
	call	ReadString			; sets EAX to the actual size of the string
	pop		edx
	pop		ecx
ENDM

;***********************************************************************
;Macro that uses Irvine's WriteString to display output. Pass a memory location as the parameter
; Note: Taken mostly from lecture and demo7
;receives: output the location with the information to display
;returns: none
;preconditions:  none
;registers changed: edx
;***********************************************************************
displayString	MACRO	output
	push	edx
	mov		edx, output
	call	WriteString
	pop		edx
ENDM

.data
array		DWORD	NUM DUP(?)		; to hold the captured integers
aString		BYTE	STRING_LENGTH DUP(?)		; to hold strings for conversions
sum			DWORD	?				; to hold the sum calculated in printSum and used in printAvg

intro		BYTE	"Hi, I'm Lisa and this is Assignment 6A: Designing Low-Level I/O Procedures!", 0
instr1		BYTE	"Please provide 10 unsigned decimal integers.", 0
instr2		BYTE	"Each number needs to be small enough to fit inside a 32 bit register.", 0
instr3		BYTE	"After you have finished inputting the raw numbers I will display a list", 0
instr4		BYTE	"of the integers, their sum, and their average value.", 0
prompt		BYTE	"Please enter an unsigned number: ", 0
problem1	BYTE	"ERROR: What you entered was not an unsigned number or it was too big.", 0
problem2	BYTE	"Please try again: ", 0
output1		BYTE	"You entered the following numbers:", 0
output2		BYTE	"The sum of these numbers is: ", 0
output3		BYTE	"The average is: ", 0
farewell	BYTE	"I hope this was helpful!", 0

.code
main PROC
	push	OFFSET intro
	push	OFFSET instr1
	push	OFFSET instr2
	push	OFFSET instr3
	push	OFFSET instr4		; pass the strings to introduction by reference
	call	introduction

	push	OFFSET prompt		; pass the prompt by reference
	push	OFFSET problem1		; pass the messages by reference to pass on to readVal
	push	OFFSET problem2	
	push	OFFSET array		; pass the array by reference
	push	OFFSET aString		; pass the string for storage in readVal by reference
	call	getInts

	push	OFFSET output1		; pass the message to printInts by reference
	push	OFFSET array		; pass the array to printInts by reference
	push	OFFSET aString		; pass the string for storage to printInts by reference
	call	printInts

	push	OFFSET output2		; pass the message to printSum by reference
	push	OFFSET array		; pass the array to printSum by reference
	push	OFFSET sum			; pass the sum variable to printSum by reference
	push	OFFSET aString		; pass the string for use with writeVal by reference
	call	printSum

	push	OFFSET output3		; pass the message to printAvg by reference
	push	sum					; pass the sum to printAvg by value
	push	OFFSET aString		; pass the string for use with writeVal by reference
	call	printAvg

	push	OFFSET farewell
	call	goodbye

	exit	; exit to operating system
main ENDP

;***********************************************************************
;Procedure to read a value from the user. Captures a string and converts it to a number, while validating that
; it contains only digits and will fit in a 32-bit register
;receives: the addresses of a prompt and two error messages, a variable location to store the result in, the address for a
; location to store the string
;returns: the number in the variable that was passed by reference
;preconditions:  none
;registers changed: ebp, esi, ecx, eax, ebx, edx, edi
;***********************************************************************
readVal		PROC
	push	ebp
	mov		ebp, esp
	push	esi
	push	ecx
	push	eax
	push	ebx
	push	edx
	push	edi

	getString	[ebp+8], STRING_LENGTH, [ebp+24]	; get string into location passed, using the constant length & prompt

	getInput:
		cmp		eax, MAX_LENGTH
		jg		notValid			; if the input length is too big to fit in a 32-bit register

		mov		esi, [ebp+8]		; put the string in esi	
		mov		ecx, eax			; use the length of the string (set to eax by ReadString in getString) as the loop counter
		mov		eax, 0				; start the accumulator at 0
		mov		edi, [ebp+12]		; put the address of the array element in edi
		cld							; to move forward through string
		convertDigit:
			push	eax
			lodsb			; get current 
			; check that it's a valid digit
			mov		ebx, 0		; clear out ebx
			mov		bl, al
			pop		eax			; restore to accumulator after it gets used for lodsb
			cmp		bl, 48
			jl		notValid
			cmp		bl, 57
			jg		notValid
			; if it is, add it to the accumulator
			mov		edx, 10
			mul		edx			; set the accumulator to 10 times itself
			jc		notValid		; if the carry bit's set it's too big so not valid - idea from Irvine's ParseDecimal32
			sub		bl, 48		; convert the character code to a digit
			add		eax, ebx		; add the new digit to the accumulator
			jc		notValid		; if the carry bit's set it's too big so not valid - idea from Irvine's ParseDecimal32	
			loop	convertDigit
		jmp		saveVal
		notValid:
			displayString	[ebp+20]
			call	Crlf
			getString	[ebp+8], STRING_LENGTH, [ebp+16]
			jmp		getInput

	saveVal:
		; save the value to return, once it's good
		mov		[edi], eax

	pop		edi
	pop		edx
	pop		ebx
	pop		eax
	pop		ecx
	pop		esi
	pop		ebp
	ret		20
readVal		ENDP

;***********************************************************************
;Procedure to display a value. Converts the number to a string and prints it with displayString
;receives: the number to print, the address for a location to store the string
;returns: none
;preconditions:  none
;registers changed: ebp, eax, edi, ebx, edx
;***********************************************************************
writeVal	PROC
	push	ebp
	mov		ebp, esp
	push	eax
	push	edi
	push	ebx
	push	edx

	; convert number to string
	mov		eax, [ebp+12]	; put the number in eax
	mov		edi, [ebp+8]	; set edi to the address of the string where it can be stored
	add		edi, STRING_LENGTH	; then point to the end of the string (from demo6)		
	dec		edi	
	std						; to move backward through the string
	mov		ebx, 10			; set divider to 10
	push	eax
	mov		al, 0
	stosb					; put a null byte to indicate the end of the string
	pop		eax
	convert:
		mov		edx, 0
		div		ebx				; divide the number by the divider (10) so the remainder in edx is the next digit
		add		edx, 48			; add 48 to the digit to convert it to character code
		push	eax
		mov		eax, edx
		stosb					; put the digit in the string (at the left end of what's currently there)
		pop		eax				; restore eax to the quotient after using it to store the digit
		cmp		eax, 0			; if the quotient is 0, you're done
		jne		convert			; otherwise, repeat the loop with the updated values		
	
	; call displayString with the string (as an address)
	inc		edi			; move it forward one because it had gone too far left
	displayString	edi

	pop		edx
	pop		ebx
	pop		edi
	pop		eax
	pop		ebp
	ret		8
writeVal	ENDP

;***********************************************************************
;Procedure to display the introductory information for the program
;receives: the addresses of 5 strings to print with displayString
;returns: none
;preconditions:  none
;registers changed: ebp
;***********************************************************************
introduction	PROC
	push	ebp
	mov		ebp,esp
	
	displayString	[ebp+24]		; intro
	call	Crlf
	call	Crlf
	displayString	[ebp+20]		; instr1
	call	Crlf
	displayString	[ebp+16]		; instr2
	call	Crlf
	displayString	[ebp+12]		; instr3
	call	Crlf	
	displayString	[ebp+8]			; instr4
	call	Crlf
	call	Crlf

	pop		ebp
	ret		20
introduction	ENDP

;***********************************************************************
;Procedure to capture the list of integers as they're entered
;receives: the address of a prompt to display, the addresses of the two messages to pass on to readVal,
; the array to fill by reference, the address of the string for readVal
;returns: the filled array in the address passed
;preconditions:  none
;registers changed: ebp, edi, esi, eax, ebx, ecx, edx
;***********************************************************************
getInts		PROC
	push	ebp
	mov		ebp, esp
	push	edi
	push	esi
	push	eax
	push	ebx
	push	ecx
	push	edx

	mov		eax, [ebp+24]			; put the adddress of the prompt in eax
	mov		ebx, [ebp+20]			; address of the first readVal message in ebx
	mov		edx, [ebp+16]			; address of the 2nd readVal message in edx
	mov		edi, [ebp+12]			; put the address of the array in edi
	mov		esi, [ebp+8]			; put the address of the string for readVal in esi
	mov		ecx, NUM				; use the size of the array as a loop counter
	
	; prompt and call readVal for every spot in the array
	getVal:
		;displayString	eax		; display the prompt
		push	eax				; the prompt
		push	ebx				; first error message
		push	edx				; second error message
		push	edi				; pass the address of the current array element by reference, used to store and return
		push	esi				; pass the address of the string for readVal
		call	readVal
		push	eax
		mov		eax, [edi]
		pop		eax
		add		edi, 4			; move to the next element
		loop	getVal

	pop		edx
	pop		ecx
	pop		ebx
	pop		eax
	pop		esi
	pop		edi
	pop		ebp
	ret		20
getInts		ENDP

;***********************************************************************
;Procedure to print the list of integers that were entered
;receives: the address of a string message to display, the array to print by reference
;returns: none
;preconditions:  none
;registers changed: ebp, esi, edi, ecx
;***********************************************************************
printInts	PROC
	push	ebp
	mov		ebp, esp
	push	esi
	push	edi
	push	ecx

	call	Crlf
	displayString	[ebp+16]		; show the message
	call	Crlf

	mov		esi, [ebp+12]			; put the address of the array in esi
	mov		edi, [ebp+8]			; put the address of the passed string in edi
	mov		ecx, NUM				; use the size of the array as the loop counter

	;print the array
	printInt:
		push	[esi]		; pass the current element to writeVal
		push	edi			; pass the address of the string for storage to writeVal by reference
		call	writeVal
		cmp		ecx, 1		; if it's the last number skip this part, otherwise add a comma and space
		je		noComma
		mov		al, ','
		call	WriteChar
		mov		al, ' '
		call	WriteChar
		noComma:
			add		esi, 4
			loop	printInt
	call	Crlf
	
	pop		ecx
	pop		edi
	pop		esi
	pop		ebp
	ret		12
printInts	ENDP

;***********************************************************************
;Procedure to print the sum of the integers that were entered
;receives: the address of a string message to display, the address of the array to sum, the address of the sum variable
; used to return, the address of the string for writeVal
;returns: the sum in the passed address, as well as printed
;preconditions:  none
;registers changed: ebp, esi, edi, eax, ebx, ecx
;***********************************************************************
printSum	PROC
	push	ebp
	mov		ebp, esp
	push	esi
	push	edi
	push	eax
	push	ebx
	push	ecx

	displayString [ebp+20]		; print the message

	mov		esi, [ebp+16]		; put the address of the array in esi
	mov		edi, [ebp+12]		; put the address of the sum variable to return in in edi
	mov		ebx, [ebp+8]		; put the address of the string for writeVal in ebx
	mov		eax, 0				; start the accumulator at 0
	mov		ecx, NUM			; set the loop counter to the size of the array

	; add the value of every element to the accumulator
	addNum:
		add		eax, [esi]		
		add		esi, 4
		loop	addNum
	
	; print the accumulator
	push	eax
	push	ebx		; the address of the string for writeVal
	call	writeVal
	call	Crlf
	; then assign it to the address in edi to return
	mov		[edi], eax

	pop		ecx
	pop		ebx
	pop		eax
	pop		edi
	pop		esi
	pop		ebp
	ret		16
printSum	ENDP

;***********************************************************************
;Procedure to print the average of the integers that were entered
;receives: the address of a string message to display, the value of the integers' sum, the address of the string for writeVal
;returns: none
;preconditions:  none
;registers changed: ebp, edi, eax, ebx, edx
;***********************************************************************
printAvg	PROC
	push	ebp
	mov		ebp, esp
	push	edi
	push	eax
	push	ebx
	push	edx

	displayString [ebp+16]		; print the message

	mov		eax, [ebp+12]		; put the value of the sum in eax
	mov		edi, [ebp+8]		; put the address of the string for writeVal in edi

	; calculate the average
	mov		edx, 0
	mov		ebx, NUM
	div		ebx					; divide the sum by the number of elements, the average is now in eax

	; print it
	push	eax
	push	edi			; the address of the string for writeVal
	call	writeVal
	call	Crlf

	pop		edx
	pop		ebx
	pop		eax
	pop		edi
	pop		ebp
	ret		12
printAvg	ENDP

;***********************************************************************
;Procedure to print a closing message
;receives: the address of the string message to display
;returns: none
;preconditions:  none
;registers changed: ebp
;***********************************************************************
goodbye		PROC
	push	ebp
	mov		ebp, esp

	call	Crlf
	displayString	[ebp+8]
	call	Crlf

	pop		ebp
	ret		4
goodbye		ENDP

END main
