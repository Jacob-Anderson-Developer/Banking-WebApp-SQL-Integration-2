       ctl-opt option(*nodebugio:*srcstmt) ;

       Dcl-F BalanceTab Disk Usage(*Input:*Output);

       Dcl-S Withdrawal Packed(15:2) Inz(*Zeros);
       Dcl-S Deposit    Packed(15:2) Inz(*Zeros);
       Dcl-S TransType  Char(1);

       DCL-PI *n;
         Amt            Packed(15:5);
         TType          Char(1);
         Balance        Packed(15:5);
         Message        Char(35);
       END-PI;

       ExSr GetBalance;

       TransType = TType;
       TransTime = %Timestamp(*Sys);
       Amount = Amt;

       If TType = 'W' And Amt > Balance;
         Message = 'Withdrawal Amount Exceeds Balance';
         *INLR = *On;
         Return;
       Else;
         Write Bal;
         Message = 'Transaction Successful';
       EndIf;

       Clear Amount;
       Clear Withdrawal;
       Clear Deposit;

       SetLL *Start BalanceTab;

       ExSr GetBalance;

       *inlr = *on;
       return;

       BegSr GetBalance;

         Read(e) BalanceTab;

         Dow Not %Eof(BalanceTab);
           If TRANSTYPE = 'W';
             Withdrawal += Amount;
           Else;
             Deposit += Amount;
           EndIf;
           Read BalanceTab;
         EndDo;

         Balance = Deposit - Withdrawal;

       EndSr;
