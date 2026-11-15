using GestionTickets.Api.Configuration;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddApiConfiguration(builder.Configuration);

var app = builder.Build();

app.UseHttpsRedirection();
app.UseRouting();
app.UseCors(CorsSettings.PolicyName);

app.Run();

// Exposes the entry point to the integration tests.
public partial class Program
{
}
