<%@ Language="C#" %>
<script runat="server" language="C#"> 
    void Page_Load()
    {
        Response.Clear();
        NameValueCollection coll = Request.QueryString;
        String pattern = ".ism";

        if (String.IsNullOrEmpty(coll.Get("token"))
            || String.IsNullOrEmpty(coll.Get("dir"))
            || coll.Get("token") != "34700ae2-7ff6-4fd6-ba79-713153a886c4")
        {
            Response.StatusCode = (int)System.Net.HttpStatusCode.Forbidden;
            return;
        }

        Response.ContentType = "text/xml";
        
        //Response.AppendHeader("Content-Type", "text/xml");
        Response.Write(@"<?xml version=""1.0"" encoding=""utf-8""?>");

        string[] files = System.IO.Directory.GetFiles(Server.MapPath("/") + coll.Get("dir") + "\\", "*" + pattern);

        Response.Write(@"<movies count=""" + files.Length.ToString() + "\">");
        
        foreach (string file in files)
        {
            System.IO.FileInfo info = new System.IO.FileInfo(file);
            String filename = info.Name.ToString();
            if (filename.EndsWith(pattern))
                Response.Write("<movie>" + info.Name + "</movie>");
        }

        Response.Write("</movies>");
    }    
</script>