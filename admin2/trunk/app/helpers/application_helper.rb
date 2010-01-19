# Methods added to this helper will be available to all templates in the application.
module ApplicationHelper
  
  def editable_content(options)
     options[:content] = { :element => 'span' }.merge(options[:content])
     options[:url] = {}.merge(options[:url])
     options[:ajax] = { :okText => "'Save'", :cancelText => "'Cancel'"}.merge(options[:ajax] || {})
     script = Array.new
     script << "new Ajax.InPlaceEditor("
     script << "  '#{options[:content][:options][:id]}',"
     script << "  '#{url_for(options[:url])}',"
     script << "  {"
     script << options[:ajax].map{ |key, value| "#{key.to_s}: #{value}" }.join(", ")
     script << "  }"
     script << ")"
  
     content_tag(
       options[:content][:element],
       options[:content][:text],
       options[:content][:options]
     ) + javascript_tag( script.join("\n") )
   end

  def choice_content(options)
     options[:content] = { :element => 'span' }.merge(options[:content])
     options[:url] = {}.merge(options[:url])
     #options[:data] = options[:data].split("||")
     options[:data] = ["yes","no"]

     script = Array.new
     script << "new Ajax.InPlaceCollectionEditor("
     script << "  '#{options[:content][:options][:id]}',"
     script << "  '#{url_for(options[:url])}',"
     script << "  { collection:#{options[:data]}"
     if ( options[:options] )
       script << ", #{options[:options]}"
     end
     script << "}"
     script << ")"
  
     content_tag(
       options[:content][:element],
       options[:content][:text],
       options[:content][:options]
     ) + javascript_tag( script.join("\n") )
   end

  
end
