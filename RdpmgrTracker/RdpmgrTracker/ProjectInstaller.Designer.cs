using System.ServiceProcess;

namespace RdpmgrTracker
{
    partial class ProjectInstaller
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary> 
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Component Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.serviceProcessInstaller1 = new System.ServiceProcess.ServiceProcessInstaller();
            this.serviceInstaller1 = new System.ServiceProcess.ServiceInstaller();
            // 
            // serviceProcessInstaller1
            // 
            this.serviceProcessInstaller1.Password = null;
            this.serviceProcessInstaller1.Username = null;


            //ServiceProcessInstaller serviceProcessInstaller = new ServiceProcessInstaller();
            //ServiceInstaller serviceInstaller = new ServiceInstaller();

            //# Service Account Information
            //this.serviceProcessInstaller1.Account = ServiceAccount.User; // and not LocalSystem;
            this.serviceProcessInstaller1.Account = ServiceAccount.LocalSystem;
            //NT AUTHORITY\NetworkService
            //NT AUTHORITY\LocalService

            // 
            // serviceInstaller1
            // 
            this.serviceInstaller1.Description = "Tracks state change for RDP Mgr";
            this.serviceInstaller1.DisplayName = "RdpmgrTracker";
            this.serviceInstaller1.ServiceName = "RdpmgrTracker";
            this.serviceInstaller1.StartType = System.ServiceProcess.ServiceStartMode.Automatic;
            // 
            // ProjectInstaller
            // 
            this.Installers.AddRange(new System.Configuration.Install.Installer[] {
            this.serviceProcessInstaller1,
            this.serviceInstaller1});

        }

        #endregion

        private System.ServiceProcess.ServiceProcessInstaller serviceProcessInstaller1;
        private System.ServiceProcess.ServiceInstaller serviceInstaller1;
    }
}