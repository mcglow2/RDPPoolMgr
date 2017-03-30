using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Diagnostics;
using System.Linq;
using System.ServiceProcess;
using System.Text;
using System.Threading.Tasks;
using System.Net;
using System.Timers;
using System.Threading;
using System.Management;
using System.Security.Principal;
using System.Diagnostics.Eventing.Reader;

namespace RdpmgrTracker
{
    public partial class RdpmgrTracker : ServiceBase
    {
        private static System.Timers.Timer heartbeatTimer;
       
        private static String RdpServiceUrl;
        private Int16 HeartbeatInterval ;

        private static EventLogWatcher watcher = null;
        private static EventLogWatcher watcher2 = null;




        public RdpmgrTracker()
        {
            CanHandleSessionChangeEvent = true;
            CanPauseAndContinue = true;
            CanHandlePowerEvent = true;

            RdpServiceUrl = "https://licmon.arc.rpi.edu/licenses-dev/rdptest/www/api/v1/"; //@Todo: Should try to read this from Registry
            HeartbeatInterval = 2;

            heartbeatTimer = new System.Timers.Timer(this.HeartbeatInterval * 60 * 1000); //  RdpMgrApi.request(RdpServiceUrl + "?method=heartbeat");
            heartbeatTimer.AutoReset = true;
            heartbeatTimer.Elapsed += new ElapsedEventHandler(HeartbeatTimer_Elapsed) ;
            //heartbeatTimer.Enabled = true;*/

            InitializeComponent();

            //subscribe();

            EventLogQuery subscriptionQuery = new EventLogQuery(
                   "Security", PathType.LogName, "*[System/EventID=4647]");  //4634

            watcher = new EventLogWatcher(subscriptionQuery);

            // Make the watcher listen to the EventRecordWritten
            // events.  When this event happens, the callback method
            // (EventLogEventRead) is called.
            watcher.EventRecordWritten +=
                new EventHandler<EventRecordWrittenEventArgs>(
                    EventLogEventRead);

            // Activate the subscription


            EventLogQuery subscriptionQuery2 = new EventLogQuery(
                   "Security", PathType.LogName, "*[System/EventID=4634]");

            watcher2 = new EventLogWatcher(subscriptionQuery2);

            // Make the watcher listen to the EventRecordWritten
            // events.  When this event happens, the callback method
            // (EventLogEventRead) is called.
            watcher2.EventRecordWritten +=
                new EventHandler<EventRecordWrittenEventArgs>(
                    EventLogEventRead);

            
        }


        protected override void OnStart(string[] args)
        {
            EventLog.WriteEntry("RdpmgrTracker.Service: Start");

            RdpMgrApi.request(RdpServiceUrl + "?method=join");

            RdpMgrApi.request(RdpServiceUrl + "?method=heartbeat");

            heartbeatTimer.Start();

            watcher.Enabled = true;
            watcher2.Enabled = true;
        }

        private void HeartbeatTimer_Elapsed(object sender, System.Timers.ElapsedEventArgs e)
        {
            RdpMgrApi.request(RdpServiceUrl + "?method=heartbeat");

        }

        

        protected override void OnStop()
        {

            EventLog.WriteEntry("RdpmgrTracker.Service: Stop");


            RdpMgrApi.request(RdpServiceUrl + "?method=leave");

            System.Threading.Thread.Sleep(5000);


            //this.heartbeatTimer.Enabled = false;
             heartbeatTimer.Stop();

            watcher.Enabled = false;
            watcher2.Enabled = false;
        }

        protected override void OnPause()
        {
            //heartbeatTimer.Enabled = false;
            heartbeatTimer.Stop();
            base.OnPause();
        }

        protected override void OnContinue()
        {
            //heartbeatTimer.Enabled = true;
            heartbeatTimer.Start();
            base.OnContinue();
        }
        

        protected override void OnShutdown()
        {
            EventLog.WriteEntry("RdpmgrTracker.Service: OS Shutting down");

            RdpMgrApi.request(RdpServiceUrl + "?method=leave");


            base.OnShutdown();
        }

        protected override void OnSessionChange(SessionChangeDescription changeDescription)
        {


            String userName1 = InteractiveUser.Account((uint)changeDescription.SessionId);
            String userName2 = null;
            String[] breakApart = userName1.ToLower().Split('\\');
            if (breakApart.Count() == 2)
            {
                userName1 = breakApart[1];
            }

            base.OnSessionChange(changeDescription);

            if (userName1.ToLower() == "" || userName1.ToLower() == "error_no_token" || userName1.ToLower() == "error_2")
            {
                Int16 MaxWait = 10000; //10 Seconds
                Int64 Waited = 0;
                Boolean goodUsername = false;

                while (Waited < MaxWait && goodUsername == false)
                {
                    userName2 = InteractiveUser.Account((uint)changeDescription.SessionId);

                    String[] breakApart2 = userName2.ToLower().Split('\\');
                    if (breakApart2.Count() == 2)
                    {
                        userName2 = breakApart2[1];
                    }

                    if (userName2.ToLower() != "" && userName2.ToLower() != "error_no_token" && userName2.ToLower() != "error_2")
                    {
                        goodUsername = true;
                        userName1 = userName2;
                    }

                }

                Waited = Waited + 500;
                Thread.Sleep(500);
            }

            String userName = userName1;
            RdpMgrApi.request(RdpServiceUrl + "?method=log&username=" + userName + "&message=" + "UNCOMPARE "+ changeDescription.SessionId.ToString() + " " + userName1 + " " + userName2 );

            /*
            //Does not work better than current method
            String machineName = Machine.getInstance().getUsername();
            RdpMgrApi.request(RdpServiceUrl + "?method=log&username=" + machineName + "&message=" + "machineNameMethod");


            if (userName == "" || userName == "error_no_token") {
                userName = machineName;
            }
            */

            EventLog.WriteEntry("RdpmgrTracker.OnSessionChange", DateTime.Now.ToLongTimeString() +
            " - Session change notice received: " +
            changeDescription.Reason.ToString() + "  Session ID: " +
            changeDescription.SessionId.ToString());
            

            switch (changeDescription.Reason)
            {
                case SessionChangeReason.ConsoleConnect:
                case SessionChangeReason.SessionLogon:
                case SessionChangeReason.RemoteConnect:
                    RdpMgrApi.request(RdpServiceUrl + "?method=logon&username=" + userName);
                    break;
                case SessionChangeReason.ConsoleDisconnect:
                case SessionChangeReason.RemoteDisconnect:
                    RdpMgrApi.request(RdpServiceUrl + "?method=disconnect&username=" + userName);
                    break;
                case SessionChangeReason.SessionLogoff:
                    RdpMgrApi.request(RdpServiceUrl + "?method=logoff&username=" + userName);
                    break;
                case SessionChangeReason.SessionLock:
                case SessionChangeReason.SessionRemoteControl:
                case SessionChangeReason.SessionUnlock:
                    break;
            }

        String message = changeDescription.Reason.ToString();
        EventLog.WriteEntry("RdpmgrTracker.OnSessionChange " + message);
        RdpMgrApi.request(RdpServiceUrl + "?method=log&username=" + userName + "&message=" + message);


        }
        
        
        // Callback method that gets executed when an event is
        // reported to the subscription.
        public static void EventLogEventRead(object obj,
            EventRecordWrittenEventArgs arg)
        {
            try
            {
                // Make sure there was no error reading the event.
                if (arg.EventRecord != null)
                {
                    EventLogRecord rec = (EventLogRecord)arg.EventRecord;

                    //String message = WebUtility.UrlEncode(rec.ToXml().ToString());
                    //RdpMgrApi.request(RdpServiceUrl + "?method=log&username=" + "" + "&message=" + message);
                    String username = "";

                    //Doesnt work for 4634
                    /*
                    try
                    {
                        SecurityIdentifier sid = rec.UserId;
                        username = sid.Translate(typeof(System.Security.Principal.NTAccount)).ToString();
                        String[] breakApart = username.ToLower().Split('\\');
                        if (breakApart.Count() == 2)
                        {
                            username = breakApart[1];
                        }

                    }
                    catch ( Exception ex) {
                        username = "ex";
                    }*/

                    
                    if ( rec.Id == 4647) {
                        try
                        {
                            username = (String)rec.Properties[1].Value;
                            //uint logonType = (uint)rec.Properties[3].Value;

                            RdpMgrApi.request(RdpServiceUrl + "?method=log&username=" + username + "&message=Event 4647  "  );
                            //Not firing for some reason
                            RdpMgrApi.request(RdpServiceUrl + "?method=logoff&username=" + username  + "&eventid=4647"  );
                            
                        }
                        catch (Exception ex2) {
                            RdpMgrApi.request(RdpServiceUrl + "?method=log&username=" + username + "&message=Event 4647 EX" + WebUtility.UrlEncode(ex2.ToString())  ); //
                        }
                    }

                    if (rec.Id == 4634)
                    {
                        username = (String)rec.Properties[1].Value;
                        uint logonType = (uint)rec.Properties[4].Value;

                        RdpMgrApi.request(RdpServiceUrl + "?method=log&username=" + username + "&message=Event 4634 LogonType " + logonType.ToString());

                       // if (logonType == 3 )
                       // {
                       //     RdpMgrApi.request(RdpServiceUrl + "?method=logoff&username=" + username + "&logonType=" + logonType.ToString() + "&eventid=4634" );
                       // }

                    }
                    


                    Debug.WriteLine("Description: ", arg.EventRecord.FormatDescription());
                }
                else
                {
                    Debug.WriteLine("The event instance was null.");

                }
            }
            catch (Exception ex) {
                Debug.WriteLine(ex.ToString());
            }


        }













    }
}
