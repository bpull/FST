import datetime as dt
from pandas.tseries.holiday import AbstractHolidayCalendar, Holiday, nearest_workday, \
    USMartinLutherKingJr, USPresidentsDay, GoodFriday, USMemorialDay, \
    USLaborDay, USThanksgivingDay
import sys

class USTradingCalendar(AbstractHolidayCalendar):
    rules = [
        Holiday('NewYearsDay', month=1, day=1, observance=nearest_workday),
        USMartinLutherKingJr,
        USPresidentsDay,
        GoodFriday,
        USMemorialDay,
        Holiday('USIndependenceDay', month=7, day=4, observance=nearest_workday),
        USLaborDay,
        USThanksgivingDay,
        Holiday('Christmas', month=12, day=25, observance=nearest_workday)
    ]


def get_trading_close_holidays(year):
    inst = USTradingCalendar()
    return inst.holidays(dt.datetime(year-1, 12, 31), dt.datetime(year, 12, 31))

def nextOpenDay(date,holidays):
    date += dt.timedelta(days=1)
    while date.weekday() >= 5 or date in holidays:
        date += dt.timedelta(days=1)
    return date

if __name__ == '__main__':
    month = int(sys.argv[1])
    year = int(sys.argv[2])
    holidays = get_trading_close_holidays(year)
    firstDay = dt.datetime(year,month,1)
    marketDates = []
    if not firstDay.weekday() >= 5 and not firstDay in holidays:
        marketDates.append(str(firstDay.date()))
    #This represents the first open day of the month
    firstDay = nextOpenDay(firstDay,holidays)

    #Goal return a list containing every day the market opens this month and the first date the market is open the next month

    marketDates.append(str(firstDay.date()))
    while firstDay.month == month:
        firstDay = nextOpenDay(firstDay,holidays)
        marketDates.append(str(firstDay.date()))
    #sys.stderr.write(marketDates[0]+'\n'+marketDates[-1])
    print marketDates

