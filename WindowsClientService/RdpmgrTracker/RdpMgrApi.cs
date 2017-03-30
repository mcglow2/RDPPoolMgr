using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Net;
using System.Diagnostics;
using System.Threading;

namespace RdpmgrTracker
{
    class RdpMgrApi
    {

        public static void request( String uri ) {
            HttpWebRequest request ;
            HttpWebResponse response;

            try
            {
                 request = (HttpWebRequest)WebRequest.Create(uri);
                request.Timeout = 10000;
                request.UserAgent = "RDPMgr " + Environment.OSVersion.ToString();
                request.KeepAlive = true;

                using (response = (HttpWebResponse)request.GetResponse()) {
                    
                }

                    //Thread.Sleep(500);

            }
            catch (Exception ex)
            {
                String e = "RdpmgrTracker.Service: EX " + ex.ToString();
                
                //Thread.Sleep(2000);
            }
            finally {
                request = null;
                response = null;
            }
            

        }





    }
}
