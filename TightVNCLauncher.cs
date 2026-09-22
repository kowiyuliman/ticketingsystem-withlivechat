using System;
using System.Diagnostics;
using System.IO;
using System.Text.RegularExpressions;
using System.Reflection;

[assembly: AssemblyTitle("TightVNC Remote Launcher")]
[assembly: AssemblyProduct("TightVNC Viewer")]
[assembly: AssemblyDescription("TightVNC Protocol Launcher")]
[assembly: AssemblyCompany("TightVNC")]
[assembly: AssemblyVersion("1.0.0.0")]

namespace TightVNCLauncher
{
    static class Program
    {
        [STAThread]
        static void Main(string[] args)
        {
            try
            {
                string raw = args.Length > 0 ? args[0] : "";
                
                // Clean up vnc:// URL
                string target = raw;
                if (!string.IsNullOrEmpty(target))
                {
                    target = Regex.Replace(target, @"^vnc://", "", RegexOptions.IgnoreCase);
                    target = Regex.Replace(target, @"^vnc:", "", RegexOptions.IgnoreCase);
                    target = target.Trim().TrimEnd('/').Trim('\\').Trim('\"').Trim('\'');
                }

                string tvnPath = @"C:\Program Files\TightVNC\tvnviewer.exe";
                if (!File.Exists(tvnPath))
                {
                    tvnPath = @"C:\Program Files (x86)\TightVNC\tvnviewer.exe";
                }

                if (File.Exists(tvnPath))
                {
                    ProcessStartInfo psi = new ProcessStartInfo();
                    psi.FileName = tvnPath;
                    if (!string.IsNullOrEmpty(target))
                    {
                        psi.Arguments = target;
                    }
                    psi.UseShellExecute = true;
                    Process.Start(psi);
                }
            }
            catch
            {
                // Silent fail-safe
            }
        }
    }
}

