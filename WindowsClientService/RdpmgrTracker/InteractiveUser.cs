using System;
using System.Collections.Generic;
using System.Linq;
using System.Runtime.InteropServices;
using System.Security.Principal;
using System.Text;
using System.Threading.Tasks;

namespace RdpmgrTracker
{
    class InteractiveUser
    {
        [DllImport("wtsapi32.dll", SetLastError = true)]
        static extern bool WTSQueryUserToken(UInt32 sessionId, out IntPtr Token);

        [DllImport("kernel32.dll")]
        private static extern UInt32 WTSGetActiveConsoleSessionId();

        enum TOKEN_INFORMATION_CLASS
        {
            TokenUser = 1,
            TokenGroups,
            TokenPrivileges,
            TokenOwner,
            TokenPrimaryGroup,
            TokenDefaultDacl,
            TokenSource,
            TokenType,
            TokenImpersonationLevel,
            TokenStatistics,
            TokenRestrictedSids,
            TokenSessionId,
            TokenGroupsAndPrivileges,
            TokenSessionReference,
            TokenSandBoxInert,
            TokenAuditPolicy,
            TokenOrigin
        }

        public struct TOKEN_USER
        {
            public SID_AND_ATTRIBUTES User;
        }

        [StructLayout(LayoutKind.Sequential)]
        public struct SID_AND_ATTRIBUTES
        {
            public IntPtr Sid;
            public int Attributes;
        }

        // Using IntPtr for pSID insted of Byte[]
        [DllImport("advapi32", CharSet = CharSet.Auto, SetLastError = true)]
        static extern bool ConvertSidToStringSid(
            IntPtr pSID,
            out IntPtr ptrSid);

        [DllImport("kernel32.dll")]
        static extern IntPtr LocalFree(IntPtr hMem);

        [DllImport("advapi32.dll", SetLastError = true)]
        static extern bool GetTokenInformation(
            IntPtr TokenHandle,
            TOKEN_INFORMATION_CLASS TokenInformationClass,
            IntPtr TokenInformation,
            int TokenInformationLength,
            out int ReturnLength);

        private static string GetSID(IntPtr token)
        {
            bool Result;

            int TokenInfLength = 0;
            string sidAsString = String.Empty;

            // first call gets lenght of TokenInformation
            Result = GetTokenInformation(token, TOKEN_INFORMATION_CLASS.TokenUser, IntPtr.Zero, TokenInfLength, out TokenInfLength);

            IntPtr TokenInformation = Marshal.AllocHGlobal(TokenInfLength);
            Result = GetTokenInformation(token, TOKEN_INFORMATION_CLASS.TokenUser, TokenInformation, TokenInfLength, out TokenInfLength);

            if (Result)
            {
                TOKEN_USER TokenUser = (TOKEN_USER)Marshal.PtrToStructure(TokenInformation, typeof(TOKEN_USER));

                IntPtr pstr = IntPtr.Zero;
                Boolean ok = ConvertSidToStringSid(TokenUser.User.Sid, out pstr);

                sidAsString = Marshal.PtrToStringAuto(pstr);
                LocalFree(pstr);
            }

            Marshal.FreeHGlobal(TokenInformation);

            return sidAsString;
        }

        public static string Account(uint sessionId)
        {
            IntPtr token = IntPtr.Zero;
            String account = String.Empty;

            if (WTSQueryUserToken(sessionId, out token)) //WTSGetActiveConsoleSessionId()
            {
                String sid = GetSID(token);
                account =
                    new SecurityIdentifier(sid).Translate(typeof(NTAccount)).ToString();
            }
            else
            {
                int err = Marshal.GetLastWin32Error();
                switch (err)
                {
                    case 5:
                        account = "ERROR_ACCESS_DENIED";
                        break;
                    case 87:
                        account = "ERROR_INVALID_PARAMETER";
                        break;
                    case 1008:
                        account = "ERROR_NO_TOKEN";
                        break;
                    case 1314:
                        account = "ERROR_PRIVILEGE_NOT_HELD";
                        break;
                    case 7022:
                        account = "ERROR_CTX_WINSTATION_NOT_FOUND";
                        break;
                    default:
                        account = String.Format("ERROR_{0}", err.ToString());
                        break;
                }
            }

            return account;
        }
    }
}
